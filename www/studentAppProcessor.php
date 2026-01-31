
<?php
require '../vendor/autoload.php';


//verify the recaptcha
$captcha;
if(isset($_POST['g-recaptcha-response'])){
  $captcha=$_POST['g-recaptcha-response'];
}
if(!$captcha){
  echo "We apologize, there was an technical error in processing your request please contact us directly at teammetalcow@gmail.com";
  exit(0);
}

$secretKey = getenv('RECAPTCHA_SECRET_API_KEY');
$ip = $_SERVER['REMOTE_ADDR'];
// post request to server
$url = 'https://www.google.com/recaptcha/api/siteverify?secret=' . urlencode($secretKey) .  '&response=' . urlencode($captcha);
$response = file_get_contents($url);
$responseKeys = json_decode($response,true);
// should return JSON with success as true
if($responseKeys["success"]) {


      //if required field is not present, prolly didn't use the HTML form.
      if( !isset($_POST['studentEmail']) ) {
        echo "We apologize, there was an technical error in processing your data please email us using
        the form at <a href='/#contact'>www.metalcowrobotics.com#contact</a>
        or directly at info@metalcowrobotics.com";
        exit(0);
      }

      //validate form data
      $studentFname = filter_var($_POST["studentFname"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentLname = filter_var($_POST["studentLname"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentEmail = filter_var($_POST["studentEmail"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentPhone = filter_var($_POST["studentPhone"], FILTER_SANITIZE_SPECIAL_CHARS);

      $parentFname = filter_var($_POST["parentFname"], FILTER_SANITIZE_SPECIAL_CHARS);
      $parentLname = filter_var($_POST["parentLname"], FILTER_SANITIZE_SPECIAL_CHARS);
      $parentEmail = filter_var($_POST["parentEmail"], FILTER_SANITIZE_SPECIAL_CHARS);
      $parentPhone = filter_var($_POST["parentPhone"], FILTER_SANITIZE_SPECIAL_CHARS);

      $studentSchool = filter_var($_POST["studentSchool"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentGrade = filter_var($_POST["studentGrade"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentRobotics = filter_var($_POST["studentRobotics"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentCommitments = filter_var($_POST["studentCommitments"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentReference = filter_var($_POST["studentReference"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentRole = filter_var($_POST["studentRole"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentSkills = filter_var($_POST["studentSkills"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentExpectations = filter_var($_POST["studentExpectations"], FILTER_SANITIZE_SPECIAL_CHARS);
      $studentInteresting = filter_var($_POST["studentInteresting"], FILTER_SANITIZE_SPECIAL_CHARS);


      //if there is an issue, redirect
      //pass back things entered.


      //setup timezone
      date_default_timezone_set('America/Chicago');

      //build the application message from the webform contents
      $htmlMessage = "MetalCow,<br>
      <br>
      The following information has been submitted via the website.<br>
      Please review and follow up with the student.<br>

      <h3>Student Contact Info</h3>
      <b>Name:</b> ".$studentFname." ".$studentLname."<br>
      <b>Email:</b> ".$studentEmail."<br>
      <b>Phone:</b> ".$studentPhone."<br>

      <h3>Parent Contact Info</h3>
      <b>Name:</b> ".$parentFname." ".$parentLname."<br>
      <b>Email:</b> ".$parentEmail."<br>
      <b>Phone:</b> ".$parentPhone."<br>

      <h3>Student Academics</h3>
      <b>School:</b> ".$studentSchool."<br>
      <b>Grade:</b> ".$studentGrade."<br>
      <b>How did student find out about MetalCow Robotics:</b> ".$studentReference."<br>
      <br>
      <b>Robotics Experience:</b><br>
      ".$studentRobotics."<br>
      <br>
      <b>Other Commitments:</b><br>
      ".$studentCommitments."<br>

      <h3>Student Team Related Info</h3>
      <b>Student is interested in a role on:</b> ".$studentRole."<br>
      <b>Student's Skills:</b><br>
      ".$studentSkills."<br>
      <br>
      <b>Student's Expectations:</b><br>
      ".$studentExpectations."<br>
      <br>
      <b>Something the student finds interesting about themself:</b><br>
      ".$studentInteresting."<br>
      <br>
      <br>
      <i>https://www.metalcowrobotics.com/pages/join.html | ".date('m/d/Y h:i:s a', time())."</i>";

      //make a connection to google to get gmail to send email for us
      $name = "MetalCow Robotics";
      $email = "teammetalcow@gmail.com";
      $from = new SendGrid\Email($name, $email);  //TODO: sending to yourself from yourself looks like hacks to google
      $subject = "MetalCow Pre-Enrollment: ".$studentFname." ".$studentLname;
      $to = new SendGrid\Email("MetalCow Robotics", getenv('TEAM_EMAIL'));
      $content = new SendGrid\Content("text/html", $htmlMessage);
      $mail = new SendGrid\Mail($from, $subject, $to, $content);

      //Send email to teammetalcow@gmail.com with the application
      $apiKey = getenv('SENDGRID_API_KEY');
      $sg = new \SendGrid($apiKey);
      $response = $sg->client->mail()->send()->post($mail);
      error_log("EmailResponse Status: ".$response->statusCode(), 0);
      error_log("EmailResponse Headers: ".$response->headers(), 0);
      error_log("EmailResponse Body: ".$response->body(), 0);
      //echo $htmlMessage;

      /**************************
      Use CURL to lazily post this to Slack and get mentors talking
      ******************/
      //slack needs it as markdown for formatting
$markdownMessage = "
>*Student Contact Info*
>Name: ".$studentFname." ".$studentLname."
>Email: ".$studentEmail."
>Phone: ".$studentPhone."
>
>*Parent Contact Info*
>Name: ".$parentFname." ".$parentLname."
>Email: ".$parentEmail."
>Phone: ".$parentPhone."
>
>*Student Academics*
>School: ".$studentSchool."
>Grade: ".$studentGrade."
>How did student find out about MetalCow Robotics: ".$studentReference."
>
>*Robotics Experience:*
>".$studentRobotics."
>
>*Other Commitments:*
>".$studentCommitments."
>
>*Student Team Related Info*
>Student is interested in a role on: ".$studentRole."
>Student's Skills:
>".$studentSkills."
>
>*Student's Expectations:*
>".$studentExpectations."
>
>*Something the student finds interesting about themself:*
>".$studentInteresting."
>
>_https://www.metalcowrobotics.com/pages/join.html | ".date('m/d/Y h:i:s a', time())."_";

      $curl_payload = ""
        ."{"
        ."\"blocks\": ["
        ."  { "
        ."    \"type\": \"section\","
        ."    \"text\": {"
        ."      \"type\": \"mrkdwn\","
        ."      \"text\": \"A new grade ".$studentGrade." student, *".$studentFname." ".$studentLname."* has applied. "
        ." \n Who will be taking point on this one? What time and day are people available to meet? "
        ." \n _:warning: (One of you will need to send the email, I can't do that yet)_\""
        ."    }"
        ."  },"
        ."  {"
        ."    \"type\": \"section\","
        ."    \"text\": {"
        ."      \"type\": \"mrkdwn\","
        ."      \"text\": \"".$markdownMessage."\""
        ."    }"
        ."  }"
        ."]"
        ."}";

        /**************************
        Make CURL post to Slack
        This is super basic... we can build on it
        later but this is a start and gets base
        connections going.
        ******************/
        $postUrl = getenv('SLACKBOT_POST_KEY');
        $ch = curl_init($postUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json'
          )
        );

        $result = curl_exec($ch);





      /**************************
      Build up an email to the student
      ******************/
      //send email to applicant with links to next available meeting times
      //look up next available times on google calendar for times labeled 'interview'

      //in the email let them select a time, if they select, update the one they picked with their info.

      //do we need some type of one time key?  How could this be hacked?

      //Build a second message to the student and parent

      //go back to the homepage
      header('Location: https://www.metalcowrobotics.com/pages/applySuccess.html');
      //die();

} else {
  echo "We apologize, there was an technical error in processing your application please contact us directly at teammetalcow@gmail.com";
  exit(0);
}
?>
