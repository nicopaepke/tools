<?php
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

class MailHelper {
	
	function sendMail($recipient, $subject, $body){ 
	require '../config.php';

		try {
			$mail = new PHPMailer(true);
	
			//Server settings            
			$mail->isSMTP();                                            
			$mail->Host       = $smtp_host;                     
			$mail->SMTPAuth   = true;                                   
			$mail->Username   = $smtp_username;                     
			$mail->Password   = $smtp_password;                               
			$mail->SMTPSecure = $smtp_secure;           
			$mail->Port       = $smtp_port;                                   
			$mail->setFrom($smtp_sender_address);

			//Recipients
			$mail->addAddress($recipient);

			//Content
			$mail->isHTML(true);                                  
			$mail->Subject = $subject;
			$mail->Body    = $body;
			$mail->AltBody = $body;

			$mail->send();
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}
}
?>