
To track exam data submission issues:

1. **Check Recent PHP Error Log**:
   - Run: Get-Content "c:/xampp/php/logs/php_error_log" -Tail 50

2. **Look for [EXAM-SUBMIT] entries to see:**
   - Is submitLessonExam() being called?
   - What score is being submitted?
   - What errors occur?

3. **Check Database State**:
   - Recent exam_attempts: SELECT * FROM exam_attempts WHERE user_id = 3 ORDER BY id DESC LIMIT 5;
   - Recent certificates: SELECT * FROM certificates WHERE user_id = 3 ORDER BY id DESC LIMIT 5;
   - Recent exam_results: SELECT * FROM exam_results WHERE user_id = 3 ORDER BY id DESC LIMIT 5;

4. **Debug Frontend Submission**:
   - Open browser Console (F12)
   - Take a test exam
   - Look for [SUBMIT-*] log entries
   - Check if network request is being sent
   - Check response status

5. **Key Questions**:
   - What score does the frontend show on completion screen?
   - Does the browser console show "[SUBMIT-RAW]" with response body?
   - Are there any JavaScript errors in the console?
   - Is the 'Continue' button working after each question?

