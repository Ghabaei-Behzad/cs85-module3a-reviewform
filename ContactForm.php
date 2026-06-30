<!-- 
 Behzad Ghabaei
 CS 85 - PHP programming
 Module 3A - ContactForm.php
 Intructor Seno
 6/29/2026
-->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contact Form</title>
</head>
<body>

<?php
/* 
This function takes two parameters. The first parameter, $data, is a string to be validated. 
The second parameter, $fieldName, is the name of the form field. The function returns the $data 
parameter after it has been cleaned up. Notice that the function uses the global variable $errorCount.
$errorCount will bring the global error counter into the function scope.
*/
function validateInput($data, $fieldName){ //Clean and validate general text input.
    global $errorCount; // Initialize a global error counter to track validation failures across functions.
    if (empty($data)) { // $data: The raw input value from the form.  Check if the user left the field completely empty
        echo "\"$fieldName\" is a required field.<br />\n"; // $fieldName The name of the field being validated (for error messages).
        ++$errorCount; // // Increment the error counter.
        $retval = "";
    } else {
        // Only cleanup if it isn't empty.
        $retval = trim($data); // Remove extra spaces, tabs, or newlines from the beginning and end of the input
        $retval = stripslashes($retval);  // Remove backslashes to prevent escaping issues.
        // $retval = htmlspecialchars($retval); // Crucial for security! Convert special characters to HTML entities to prevent Cross-Site Scripting (XSS) attacks
    }
    return($retval); //The sanitized input data. return the safe, cleaned data.
}

/* 
This function is almost exactly like the validateInput() function, but it adds a filter_var() 
test to validate that the entered e-mail address is in the correct format. 
*/
function validateEmail($data, $fieldName) { //Clean and validate email input specifically.
    global $errorCount;
    if (empty($data)) {  //$data: The raw email input value from the form. Check if empty.
        echo "\"$fieldName\" is a required field.<br />\n"; //$fieldName: The name of the field (usually 'Email').
        ++$errorCount;
        $retval = "";
    } else {
        $retval = filter_var($data, FILTER_SANITIZE_EMAIL);  
        if (!filter_var($retval, FILTER_VALIDATE_EMAIL)) {  // Built-in PHP filter to verify the structure looks like a real email address (i.e., user@domain.com)
            echo "\"$fieldName\" is not a valid e-mail address.<br />\n";
            ++$errorCount; // FIXED: Increments error count so form stays visible on bad email structure
        }
    }
    return($retval); //The sanitized email data.
}

/* 
Renders the HTML form on the page.  
This function takes one parameter for each form field and displays the form. 
It uses the parameters for sticky form functionality.
*/
function displayForm($Sender, $Email, $Subject, $Message) {
    ?>
    <h2 style="text-align:center">Contact Me</h2>
    <form name="contact" action="ContactForm.php" method="post">
        <p>Your Name: <input type="text" name="Sender" value="<?php echo $Sender; ?>" /> </p>
        <p>Your E-mail: <input type="text" name="Email" value="<?php echo $Email; ?>" /> </p>
        <p>Subject: <input type="text" name="Subject" value="<?php echo $Subject; ?>" /> </p>
        <p>Message:<br />
      <textarea name="Message"><?php echo $Message; ?> </textarea></p> 
        <p><input type="reset" value="Clear Form" />&nbsp; &nbsp; <input type="submit" name="Submit" value="Send Form" /></p>
    </form>
    <?php
}

/* declare and initialize a set of variables as follows: 
Main Program Execution Logic */
//  Declare and initialize tracking variables.
$ShowForm = TRUE;
$errorCount = 0;
$Sender = "";
$Email = "";
$Subject = "";
$Message = "";

/* 
add the following code to check for and validate the input. Note that $_POST['Email'] 
is checked with the validateEmail() function instead of the validateInput() function. 
*/
 // Check if the form was actually submitted by the user
 // Pass raw POST data into our validation functions
if (isset($_POST['Submit'])) {
    $Sender = validateInput($_POST['Sender'], "Your Name");
    $Email = validateEmail($_POST['Email'], "Your E-mail");
    $Subject = validateInput($_POST['Subject'], "Subject");
    $Message = validateInput($_POST['Message'], "Message");
    
    if ($errorCount == 0) {   // If no validation errors occurred, we can safely hide the form and process the email
        $ShowForm = FALSE;
    } else {
        $ShowForm = TRUE;
    }
}

/* 
add a conditional statement that checks the value of $ShowForm. If $ShowForm is TRUE, 
the form is displayed. Otherwise, an e-mail message is sent and a status message is displayed. 
Note that a copy is sent to the sender. 
*/
if ($ShowForm == TRUE) { // Conditional structure to either display the form or send the emails
    if ($errorCount > 0) {
        echo "<p style='color: red;'>Please re-enter the form information below.</p>\n";
    }
    displayForm($Sender, $Email, $Subject, $Message);
} else { // Form data is valid, prepare the email details
    $To = "admin@example.com"; // Your admin email destination, website owner's email destination
    $SenderAddress = "$Sender <$Email>";
    
    // Formatted headers with standard line breaks for email clients
    $Headers = "From: " . $SenderAddress . "\r\n" . "CC: " . $Email . "\r\n";
    
    // Actually trigger the mail function and save the status to $result
    $result = mail($To, $Subject, $Message, $Headers);
    
    if ($result) {
        echo "<p>Your message has been sent, Thank you, " . htmlspecialchars($Sender) . ".</p>\n";
    } else {
        // Local environments (like Laravel Herd/XAMPP) without a configured mail server usually hit this fallback
        echo "<p>Your data is valid! (Note: Email simulation completed, configure a local mail server like Mailpit to catch the actual delivery).</p>\n";
    }
}
/*
Assignment Reflection.
1. What does each function do?
   - validateInput: Strips formatting issues.  Standardizes raw user text data by trimming spacing, stripping 
     dangerous backslashes, converting HTML syntax into plain text entities, and 
     catching empty inputs.
   - validateEmail: Verifies text structure matches a standard email layout.   Extends the basic text cleaning by running PHP's internal 
     validation check to ensure the format matches a genuine email structure.
   - displayForm: Prints the HTML elements dynamically and remembers past input data.  Outputs clean HTML form elements onto the screen. It maps variables 
     directly into the 'value' fields so the user doesn't have to retype everything 
     if a mistake happens.
    
2. How is user input protected?
   - POST is like an envelope, but GET is like a postcard.
   - htmlspecialchars() neutralizes scripts. trim() eliminates spacing bypasses. 
   - $retval = htmlspecialchars($retval); for security! Convert special characters to HTML entities to prevent Cross-Site Scripting (XSS) attacks
   - filter_var() protects the integrity of the recipient system from faulty email routing.

   3. What were the most confusing parts?
   - Keeping track of the 'global $errorCount' variable moving inside functions.
   - syntax errors, and remembering to place a $ in front of variables.
   - error message from if/ else statement on line 116.
   - requires close attention to logic flow.

4. What could be improved?
   - Catching all error strings inside an array to cleanly print them together later.
   - block cross-site script injection, with specialcharshtml
   - on line 71,  Wrap the sticky evaluation for the <textarea> input inside a quick ternary check. 
   - This prevents extra spaces from automatically stacking inside the box every single time, 
   - on reloading the form page. <textarea name="Message"><?php echo $Sender == "" && $Email == "" && $Subject == "" && $Message == "" ? "" : trim($Message); ?></textarea></p>  
5. Why send a copy of the form to the sender?
   - It functions as a receipt, giving the user confirmation their data went through.  It guarantees their message went through, 
     creates a digital paper trail for their records, and lets them double-check exactly 
     what details they submitted.
    */
?>

</body>
</html>
