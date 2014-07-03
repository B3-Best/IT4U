<?php
	if(!isset($_SESSION)) {
		session_start();
	}

	/*
	 * ToDos
	 * - MySQL(i) einbinden -> require_once()
	 * - Query Befehle müssen ausgeführt werden -> sql() mit entsprechender richtiger Funktion ersetzen
	 * - PHPmailer downloaden, damit es eingebunden werden kann
	*/
	require_once("class.mysqli.php"); // FIX ME
	require_once("phpmailer/class.phpmailer.php"); // DATEI(EN) FEHLT
/**
* Class for user management
* @author	Sebastian Krätzig
* @package	userMgmt
* @copyright	Since 2014 by Sebastian Krätzig
* @link		https://github.com/B3-Best/Assetone
*/
class userMgmt {
	/**
	* Checks login status of user
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->loginStatus();
	* </code>
	* @return	boolean true or false
	*/
	private function loginStatus() {
		if(isset($_SESSION["loggedIn"])) {
			if($_SESSION["loggedIn"] == 1) {
				return 1;
			}
		}
		return 0;
	}

	/**
	* Login user
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->login("max", "very$SecretPassword2014");
	* </code>
	* @return	boolean true or false
	*/
	public function login($username, $password) {
		$username = trim($username);
		$password = trim($password);

		$query = "SELECT B_ID, B_Vorname, B_Nachname, B_email, B_Username, B_Passwort, B_LastLogin, B_Resethash FROM Benutzer WHERE B_Username=\"$username\"";

		if((!empty($username)) AND (!empty($password))) {
			if($userDetails = sql($query)) {
				if(md5($password) === $userDetails["B_Passwort"]) {
					$_SESSION["loggedIn"] = 1;
					$_SESSION["userDetails"] = $userDetails;

					return 1;
				}
			}
		}
		return 0;
	}

	/**
	* Logout user and destroy session
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->logout();
	* </code>
	* @return	boolean true or false
	*/
	public function logout() {
		if(session_destroy()) {
			return 1;
		}
		return 0;
	}

	/**
	* Sends 'Password forgot'-Link to user
	* @author	Sebastian KrÃtzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->sendPasswordForgotLink("max", "max@mustermann.com");
	* </code>
	* @return	boolean true or false
	*/
	public function sendPasswordForgotLink($username, $email) {
		$queryUserID = "SELECT B_ID FROM Benutzer WHERE B_Username=\"$username\" AND B_email=\"$email\"";

		if($userID = sql($queryUserID)) {
			$userID = $userID["B_ID"];

			$randomResetHash = md5($this->randomString(20));

			$querySaveResetHash = "UPDATE Benutzer SET B_Resethash=\"$randomResetHash\" WHERE B_ID=\"$userID\"";

			if(sql($querySaveResetHash)) {
				$mail = new PHPMailer;

				$mail->CharSet = 'utf-8';
				$mail->From = "noReply@" . $_SERVER['SERVER_NAME'];
				$mail->FromName = 'Assetone';
				$mail->AddAddress("$user_email", "$first_name $last_name"); // Add a recipient

				$mail->IsHTML(true); // Set email format to HTML

				$mail->Subject = 'Assetone - Passwort zurücksetzen';

				$email_message_html = "Hallo $B_Vorname $B_Nachname,<br><br>";
				$email_message_html .= "es wurde eine Passwort-Änderung Ihres Assetones Accounts angefordert. Sie können das Passwort über den nachfolgenden Link ändern<br>";
				$email_message_html .= "------------------<br>";
				$email_message_html .= "<a href=\"$http_type://" . $_SERVER['SERVER_NAME'] . "$root_url/changeforgottenpassword.php?rstpw=true&hash=$random_password_hash&uid=" . $id . "\">Passwort ändern</a><br>";
				$email_message_html .= "Benutzername: $username<br>";
				$email_message_html .= "------------------<br><br>";
				$email_message_html .= "Sollten Sie keine Passwort-Änderung angefordert haben, ignorieren Sie diese E-Mail bitte.<br><br><br>";
				$email_message_html .= "Mit freundlichen Grüßen,<br>";
				$email_message_html .= "Assetone System<br<br>>";
				$email_message_html .= "Ihr Assetone System: <a href=\"$http_type://" . $_SERVER['SERVER_NAME'] . "$root_url/\">" . $_SERVER['SERVER_NAME'] . "</a><br><br>";
				$email_message_html .= "Projekt auf GitHub: <a href=\"https://github.com/B3-Best/Assetone/\">https://github.com/B3-Best/Assetone</a>";

				$mail->Body = "$email_message_html";

				if($mail->Send()) {
					return 1;
				}
			}
		}
		return 0;
	}

	/**
	* Validates requested link
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->validatePasswordResetLink();
	* </code>
	* @return	boolean true or false
	*/
	private function validatePasswordResetLink() {
		if($_SERVER["REQUEST_URI"] != $_SERVER["SCRIPT_NAME"]) {
			if($_GET["rstpw"] == "true") {
				$queryResetHash = "SELECT B_Resethash FROM Benutzer WHERE B_ID=\"" . $_GET["uid"] . "\"";

				$resetHash = sql($queryResetHash);

				if($resetHash["B_Resethash"] == $_GET["hash"]) {
					return 1;
				}
			}
		}
		return 0;
	}

	/**
	* Saves the new password in the database and sends an email to the user
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->saveResetedPassword(3, "new!Secret$Password2014");
	* </code>
	* @return	boolean true or false
	*/
	public function saveResetedPassword($userID, $newPassword) {
		$newPassword = md5($newPassword);

		$querySavePassword = "UPDATE Benutzer SET B_Resethash=\"\", B_Passwort=\"$newPassword\" WHERE B_ID=\"$userID\"";

		if(sql($querySavePassword)) {
			return 1;
		}
		return 0;
	}

	/**
	* Returns the first and last name or the group of the logged in user
	* @author	Sebastian KrÃtzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->saveResetedPassword(1, 3);
	* </code>
	* @return	string first and last name or group
	* @param	integer $type use 1 for first and last name and 2 for group name
	 */
	public function getUserDetails($type, $userID) {
		if($type == 1) {
			$queryGetFirstAndLastName = "SELECT B_Vorname, B_Nachname FROM Benutzer WHERE B_ID=\"$userID\"";

			if($userDetails = sql($queryGetFirstAndLastName)) {
				return $userDetails;
			}
		}
		elseif($type == 2) {
			$queryGetGroup = "SELECT Benutzergruppen.Bg_Bezeichnung FROM Benutzergruppen LEFT JOIN Benutzer ON Benutzergruppen.Bg_ID=Benutzer.Bg_ID WHERE Benutzer.B_ID=\"$userID\"";
			/*
			 * SELECT Bg_ID FROM Benutzer WHERE B_ID="3";
			 * SELECT Bg_Bezeichnung FROM Benutzergruppen WHERE Bg_ID="Bg_ID";
			 * */

			if($userGroup = sql($queryGetGroup)) {
				return $userGroup["Bg_Bezeichnung"];
			}
		}
		return 0;
	}

	/**
	 * Adds new user to the database
	 * @author	Sebastian Krätzig
	 * @package	userMgmt
	 * @example
	 * <code>
	 * $userMgmt = new user();
	 * $userMgmt->addUser("Max", "Mustermann", "max@mustermann.de", "mmustermann", "very!Secret$Password2014", 3);
	 * </code>
	 * @return	boolean true or false
	 */
	public function addUser($firstName, $lastName, $email, $username, $password, $groupID) {
		$firstName = trim($firstName);
		$lastName = trim($lastName);
		$email = trim($email);
		$username = trim($username);
		$password = md5(trim($password));
		$groupID = trim($groupID);

		$queryAddUser = "INSERT INTO Benutzer (B_Vorname, B_Nachname, B_email, B_Username, B_Passwort, Bg_ID) VALUES (\"$firstName\", \"$lastName\", \"$email\", \"$username\", \"$password\", \"$groupID\")";

		if(sql($queryAddUser)) {
			return 1;
		}

		return 0;
	}

	/**
	* Deletes existing user from database
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->deleteUser(3);
	* </code>
	* @return	boolean true or false
	 */
	public function deleteUser($userID) {
		$queryDeleteUser = "DELETE FROM Benutzer WHERE B_ID=\"$userID\"";

		if(sql($queryDeleteUser)) {
			return 1;
		}
		return 0;
	}

	/**
	* Checks, if the user has "computer systems supervisor" permissions
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->isUserComputerSystemsSupervisor();
	* </code>
	*/
	private function isUserComputerSystemsSupervisor($userID) {
		if($this->getUserDetails(2, $userID) == "Systembetreuer") {
			return 1;
		}
		return 0;
	}

	/**
	* Checks, if the user has "apprentice" permissions
	* @author      Sebastian Krätzig
	* @package     userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->isUserApprentice();
	* </code>
	*/
	private function isUserApprentice($userID) {
		if($this->getUserDetails(2, $userID) == "Auszubildender") {
			return 1;
		}
		return 0;
	}

	/**
	* Checks, if the user has "teacher" permissions
	* @author      Sebastian Krätzig
	* @package     userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->isUserTeacher();
	* </code>
	*/
	private function isUserTeacher($userID) {
		if($this->getUserDetails(2, $userID) == "Lehrer") {
			return 1;
		}
		return 0;
	}

	/**
	* Checks, if the user has "management" permissions
	* @author	Sebastian Krätzig
	* @package	userMgmt
	* @example
	* <code>
	* $userMgmt = new userMgmt();
	* $userMgmt->isUserManagement();
	* </code>
	*/
	private function isUserManagement($userID) {
		if($this->getUserDetails(2, $userID) == "Verwaltung") {
			return 1;
		}
		return 0;
	}
}
?>
