 CAMPUS MANAGEMENT SYSTEM
 
 Private Higher Education Institution
================================================================================

WHAT YOU NEED TO RUN THIS SYSTEM
--------------------------------
- XAMPP (with Apache and PHP)
- Any web browser (Chrome, Firefox, Edge all work fine)
- About 5 minutes to set up

HOW TO GET IT RUNNING
---------------------
1. Take the "campus_management_system" folder and drop it into:
   C:\xampp\htdocs\

2. Open XAMPP Control Panel and hit "Start" next to Apache

3. Open your browser and type:
   http://localhost/campus_management_system/

That's it. You should see the main menu with three options.

WHAT EACH FILE DOES
-------------------
index.php       - The main menu screen where you pick a module
parking.php     - Handles all parking permit stuff
library.php     - Manages book borrowing and late fines
performance.php - Tracks student marks and calculates averages
functions.php   - Shared tools used by all modules (don't edit unless you know what you're doing)

================================================================================
PARKING PERMITS - HOW IT WORKS
================================================================================

What you can do here:
- Issue parking permits to students, staff, or visitors
- Students pay R450, staff pay R750, visitors pay R100
- No permits for anyone under 18 (system checks this automatically)
- Max 100 permits total

Steps to issue a permit:
1. Type in the person's full name
2. Enter their age (must be 18 or older)
3. Pick which type of permit they need
4. Click "Issue Permit"

What you'll see after:
- How many students, staff, and visitors have permits
- Total money collected from all permits
- How many parking spots are left (capacity bar fills up)

If something goes wrong:
- Under 18? System says no and explains why
- Parking full? Tells you there's no space left

The "Reset Data" button clears everything and starts fresh.

================================================================================
LIBRARY SYSTEM - BORROWING AND FINES
================================================================================

What this module does:
- Lets multiple people borrow books
- Different book types have different late fees:
  * Textbooks     - R5 for each day late
  * Journals      - R3 for each day late  
  * Reference     - R10 for each day late
- Calculates fines automatically when you return books late
- Blocks borrowing if someone owes more than R200 in fines

How to borrow a book:
1. Enter a User ID (make one up like STU001 or STAFF01)
2. Enter the person's name
3. Type the book title
4. Choose what kind of book it is (Textbook/Journal/Reference)
5. Say how many days they want to keep it
6. Click "Borrow"

How to return a book:
1. Pick the book from the dropdown list
2. Click "Return"
3. System tells you if there's a fine and how much

How to pay a fine:
1. Enter the User ID
2. Type how much they want to pay
3. Click "Pay"

Want to see someone's borrowing history?
- Type their User ID in the "View User Summary" box
- Click "View"
- Shows all books they've borrowed, when they returned them, and any fines

The fine rule:
- If someone owes more than R200, they CANNOT borrow more books until they pay
- Pay even part of the fine and they can borrow again

================================================================================
STUDENT PERFORMANCE - MARKS AND STATISTICS
================================================================================

Right when you open this module, you'll see 4 students already loaded:
- Alice Johnson (Computer Science) - 8 marks
- Bob Smith (Business) - 8 marks  
- Carol Williams (Engineering) - 8 marks
- David Brown (IT) - 8 marks

What the system calculates for you:
- Each student's average mark
- Their result: Distinction (75%+), Pass (50-74%), or Fail (below 50%)
- Who's the top student (shown with a trophy)
- Class average, highest average, lowest average
- How many got Distinction, Pass, or Fail

Adding a new student:
1. Click "Register Student"
2. Give them an ID (like STU005)
3. Type their name and course
4. Click "Register"

Adding marks:
1. Pick the student from the dropdown
2. Type the subject name (like "Mathematics")
3. Enter the mark (between 0 and 100)
4. Click "Add Mark"

The system won't let you enter marks below 0 or above 100. It'll tell you if you try.

Want to see all of a student's marks?
- Just click on their row in the table
- Their full mark list drops down below
- Click again and it hides

The "Reset to Default" button brings back the original 4 students with their 8 marks each.

================================================================================
QUICK NOTES ABOUT DATA STORAGE
================================================================================

Everything saves in your browser's session memory. This means:
- Your data stays while you're using the system
- Close your browser? Data disappears
- Open a different browser? It's a fresh start

The system keeps everything in sessions:
- Parking permits in one session array
- Library records in another
- Student marks in another

================================================================================
FIXING COMMON PROBLEMS
================================================================================

"Page not found"
- Check that your folder is really in htdocs
- Make sure you typed the URL correctly

"Blank white screen"
- Restart Apache in XAMPP
- Sometimes PHP just needs a refresh

Session errors or "undefined array key" messages
- Click the "Reset" button in whichever module is giving trouble
- That usually fixes it right away

Can't borrow a book even though fines are paid?
- Try refreshing the page
- The system updates immediately when you pay

Marks not showing up?
- Make sure you selected a student from the dropdown
- Check that you entered a number between 0 and 100

================================================================================
FINAL NOTES
================================================================================

This system fulfills all assignment requirements:
- Three integrated modules sharing data through functions.php
- Parking permits with age check, capacity limit, and revenue tracking
- Library system with fines, categories, and borrowing blocks
- Student performance with 4 students, 8 marks each, averages, and statistics

Everything runs on XAMPP with no database needed. Just PHP sessions.

================================================================================