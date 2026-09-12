$<?php
session_start();
$_GET["topic"] = "ENGLISH.yaml";
$pair = $_GET["pair"] ?? $_SESSION["active_pair"] ?? "en-rw";
$direction = $_GET["direction"] ?? $_SESSION["active_direction"] ?? "en";
var_dump($pair);
var_dump($direction);

