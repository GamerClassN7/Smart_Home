<?php
class EmailManager {
	public function SendTo ($pro, $predmet, $zprava) {
			$email = "From: EnergyCounter@steelants.cz";
			$email .= "\nMIME-Version: 1.0\n";
			$email .= "Content-Type: text/html; charset=\"utf-8\"\n";
			if (!mb_send_mail ($pro, $predmet, $zprava, $email)) {
				throw new PDOException("!Email se nepodařilo odeslat!");
			}
		}
}
