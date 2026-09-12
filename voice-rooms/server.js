const path = require('path');
const http = require('http');
const crypto = require('crypto');
const express = require('express');
const cors = require('cors');
const mysql = require('mysql2/promise');
const { WebSocketServer } = require('ws');

const app = express();
app.use(cors());
app.use(express.static(path.join(__dirname, 'public')));

const server = http.createServer(app);
const wss = new WebSocketServer({ server, perMessageDeflate: false });
const rooms = new Map();
const authSecret = process.env.VOICE_ROOMS_SECRET || 'playmates-voice-rooms-local-secret';
const db = mysql.createPool({
  host: process.env.DB_HOST || '127.0.0.1',
  port: Number(process.env.DB_PORT || 3306),
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'playmates',
  waitForConnections: true,
  connectionLimit: 5
});

const databaseReady = initializeDatabase();

async function initializeDatabase() {
  await db.execute(`
    CREATE TABLE IF NOT EXISTS voice_rooms (
      name VARCHAR(80) PRIMARY KEY,
      topic VARCHAR(100) NOT NULL DEFAULT '',
      description VARCHAR(300) NOT NULL DEFAULT '',
      room_limit TINYINT UNSIGNED NOT NULL DEFAULT 6,
      created_by BIGINT UNSIGNED NOT NULL,
      created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
  `);
}

wss.on('connection', (ws) => {
  ws.userId = `user_${Math.random().toString(36).slice(2, 10)}`;
  ws.isAlive = true;

  ws.on('pong', () => { ws.isAlive = true; });
  ws.on('message', (raw) => {
    try {
      const message = JSON.parse(raw.toString());
      if (message.type === 'JOIN_ROOM') joinRoom(ws, message).catch((error) => {
        console.error('Could not join room:', error.message);
        send(ws, { type: 'ROOM_ERROR', message: 'Unable to join this room right now.' });
      });
      else if (message.type === 'LEAVE_ROOM') leaveRoom(ws);
      else if (message.type === 'GET_ROOMS') sendRoomList(ws);
      else if (['OFFER', 'ANSWER', 'CANDIDATE', 'AUDIO_LEVEL', 'CHAT_MESSAGE', 'ROOM_SETTINGS', 'MUTE_PARTICIPANT', 'REMOVE_PARTICIPANT'].includes(message.type)) {
        forwardMessage(ws, message);
      }
    } catch (error) {
      console.error('Invalid WebSocket message:', error.message);
    }
  });
  ws.on('close', () => leaveRoom(ws));
});

function verifyAuthToken(token) {
  if (typeof token !== 'string') return null;
  const [payload, signature] = token.split('.');
  if (!payload || !signature) return null;
  const expected = crypto.createHmac('sha256', authSecret).update(payload).digest('hex');
  if (signature.length !== expected.length || !crypto.timingSafeEqual(Buffer.from(signature), Buffer.from(expected))) return null;
  try {
    const data = JSON.parse(Buffer.from(payload, 'base64url').toString('utf8'));
    return data.expiresAt > Math.floor(Date.now() / 1000) ? Number(data.userId) : null;
  } catch {
    return null;
  }
}

async function joinRoom(ws, message) {
  await databaseReady;
  leaveRoom(ws);
  const roomName = String(message.room || 'lobby').trim().slice(0, 80) || 'lobby';
  const accountId = verifyAuthToken(message.authToken);
  if (!accountId) return send(ws, { type: 'AUTH_REQUIRED', message: 'Please sign in before joining a room.' });
  const [users] = await db.execute('SELECT id, full_name FROM users WHERE id = ? LIMIT 1', [accountId]);
  if (!users.length) return send(ws, { type: 'AUTH_REQUIRED', message: 'Your account could not be found. Please sign in again.' });
  const username = String(users[0].full_name || 'Member').trim().slice(0, 40) || 'Member';
  const requestedLimit = Math.max(2, Math.min(20, Number(message.limit) || 6));
  await db.execute(`
    INSERT INTO voice_rooms (name, topic, description, room_limit, created_by)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE name = name
  `, [roomName, String(message.topic || '').slice(0, 100), String(message.description || '').slice(0, 300), requestedLimit, accountId]);
  const [savedRooms] = await db.execute('SELECT topic, description, room_limit FROM voice_rooms WHERE name = ?', [roomName]);
  const savedRoom = savedRooms[0];
  if (!rooms.has(roomName)) rooms.set(roomName, { members: new Map(), hostId: ws.userId, topic: savedRoom.topic, description: savedRoom.description, limit: savedRoom.room_limit });
  const room = rooms.get(roomName);
  if (room.members.size >= room.limit && !room.members.has(ws.userId)) return send(ws, { type: 'ROOM_ERROR', message: 'This room is full.' });
  const peers = [...room.members.values()].map(({ userId, username: peerName, muted }) => ({ userId, username: peerName, muted: Boolean(muted) }));
  room.members.set(ws.userId, { ws, userId: ws.userId, username, muted: false });
  ws.currentRoom = roomName;
  ws.username = username;
  ws.accountId = accountId;
  send(ws, { type: 'ROOM_JOINED', room: roomName, userId: ws.userId, peers, hostId: room.hostId, topic: room.topic, description: room.description, limit: room.limit });
  broadcast(room, ws.userId, { type: 'NEW_PEER', peer: { userId: ws.userId, username } });
  broadcastRoomLists();
}

function leaveRoom(ws) {
  if (!ws.currentRoom) return;
  const room = rooms.get(ws.currentRoom);
  if (room) {
    room.members.delete(ws.userId);
    if (room.hostId === ws.userId) {
      room.hostId = room.members.keys().next().value;
      if (room.hostId) send(room.members.get(room.hostId).ws, { type: 'HOST_CHANGED', hostId: room.hostId });
    }
    broadcast(room, ws.userId, { type: 'PEER_LEFT', peerId: ws.userId });
    if (room.members.size === 0) rooms.delete(ws.currentRoom);
    broadcastRoomLists();
  }
  ws.currentRoom = null;
}

function forwardMessage(ws, message) {
  const room = rooms.get(ws.currentRoom);
  if (!room) return;
  if (['MUTE_PARTICIPANT', 'REMOVE_PARTICIPANT'].includes(message.type)) {
    if (ws.userId !== room.hostId || !room.members.has(message.target) || message.target === ws.userId) return;
    const target = room.members.get(message.target);
    if (message.type === 'MUTE_PARTICIPANT') {
      const muted = message.muted !== false;
      target.muted = muted;
      send(target.ws, { type: 'FORCE_MUTE', sender: ws.userId, muted });
      broadcast(room, ws.userId, { type: 'PARTICIPANT_MUTED', peerId: message.target, muted });
      send(ws, { type: 'PARTICIPANT_MUTED', peerId: message.target, muted });
    } else {
      send(target.ws, { type: 'REMOVED_FROM_ROOM', message: 'The room host removed you.' });
      room.members.delete(message.target);
      target.ws.currentRoom = null;
      broadcast(room, ws.userId, { type: 'PEER_LEFT', peerId: message.target });
      broadcastRoomLists();
    }
    return;
  }
  if (message.type === 'ROOM_SETTINGS') {
    if (ws.userId !== room.hostId) return;
    room.topic = String(message.topic || '').slice(0, 100);
    room.description = String(message.description || '').slice(0, 300);
    room.limit = Math.max(2, Math.min(20, Number(message.limit) || 6));
    db.execute('UPDATE voice_rooms SET topic = ?, description = ?, room_limit = ? WHERE name = ?', [room.topic, room.description, room.limit, ws.currentRoom]).catch((error) => console.error('Could not save room settings:', error.message));
  }
  const outgoing = { ...message, sender: ws.userId, username: ws.username || 'Guest' };
  if (message.target && room.members.has(message.target)) send(room.members.get(message.target).ws, outgoing);
  else broadcast(room, ws.userId, outgoing);
}

function sendRoomList(ws) {
  send(ws, { type: 'ROOM_LIST', rooms: getRoomList() });
}

function broadcastRoomLists() {
  const message = { type: 'ROOM_LIST', rooms: getRoomList() };
  wss.clients.forEach(ws => send(ws, message));
}

function getRoomList() {
  return [...rooms.entries()].map(([name, room]) => ({
    name,
    count: room.members.size,
    limit: room.limit,
    members: [...room.members.values()].map(({ userId, username, muted }) => ({ userId, username, muted: Boolean(muted) }))
  }));
}

function broadcast(room, senderId, message) {
  room.members.forEach(({ ws, userId }) => {
    if (userId !== senderId) send(ws, message);
  });
}

function send(ws, message) {
  if (ws.readyState === 1) ws.send(JSON.stringify(message));
}

const heartbeat = setInterval(() => {
  wss.clients.forEach((ws) => {
    if (!ws.isAlive) return ws.terminate();
    ws.isAlive = false;
    ws.ping();
  });
}, 30000);
wss.on('close', () => clearInterval(heartbeat));

const port = process.env.PORT || 10000;
databaseReady.then(() => server.listen(port, () => console.log(`PlayMates Voice Rooms running at http://localhost:${port}`))).catch((error) => {
  console.error('Voice Rooms database connection failed:', error.message);
  process.exitCode = 1;
});
