<?php

/**
 * Name         : FW_API_FREGGERSANALYSER
 * Beschreibung : Ermöglicht das auslesen bestimmter Informationen vom Freggers Server
 * Version      : 0.1
 * Autor        : Bernhard Eisele 2011
 * License      : CC BY-SA 3.0 (http://creativecommons.org/licenses/by-sa/3.0/de/)

  ACHTUNG ZUM AUSLESEN VON DATEN BRAUCHT IHR DIE ERLAUBNIS VON DEN MACHERN VON FREGGERS
  => http://www.freggers.de/help/imprint

  Versionshistory
  0.1 RC1
  -Auslesen aller wichtigen Daten
  0.1 RC2
  -Auslesen der Daten von Garten des Fauns
  0.1
  -Nun Objektorentiert.
  -Fix Abzeichen Gesamt + Abzeichen fertig
  -Entfernen un�tiger Variablen
  0.2
  -Fixe Geschlechtererkennung
  -Kommentierung verbessert

  Funktionsbeschreibung:

  $neuerFregger = new Freggeranalyser (Sting mit Freggersname, String mit Freggersserver, true or false);

  Freggersname     => Hier wird angegeben welcher Freggersname analysiert werden soll
  Freggersserver   => Hier wird angegeben auf welchem Server sich der Freggers besfindet (Nur Domainendung de oder com angeben)
  true - false     => Hier wird angegeben ob alles analysiert wird oder man kann die einzelnen Schritte auch per Hand durchgehen
  $neuerFregger->getProfile();
  $neuerFregger->getHP();
  $neuerFregger->getGeschlecht(); //Benötigt getProfile();

  Rückgabe:
  Wenn der Freggers nicht existiert werden leere Felder zur�ckgegeben

  Zurückgabe bei einem existierenden Freggers mit Beispielwerten:
  ->data(
  //Ab hier getProfile();
  [NAME] => freggern
  [SERVER] => de
  [ID] => 428488
  [ALTER] => 0
  [ORT] => Puck / Azubi-Garten
  [ONLINE] => 1
  [FREETOADS] => 55039
  [ONLINESTUNDEN] => 3171
  [INVENTARWERT] => 8570
  [WEBSEITE] => http://www.freggers-wiki.de/umfrage/index.php?sid=51929
  [FREUNDE] => 6
  [FREUNDEONLINE] => 0
  [SERVEROP] =>
  [ADMIN] =>
  [ZIMMERANZAHL] => 4
  [TEAMNAME] => Puck
  [TEAMMITGLIEDER] => 4713
  [FAUNLEVEL] => 7

  //Ab hier getHP();
  [ABZEICHENGESAMT] => 31
  [ABZEICHENFERTIG] => 27

  //Ab hier getGeschlecht();
  [GESCHLECHT] => 2
  );

  echo $neuerFregger->data["NAME"];
 * */

namespace FreggerWiki\API;

use FreggersWiki\API as FW_API;

//error_reporting(E_ERROR | E_WARNING | E_PARSE ); // debug line

class Freggeranalyser {

	/**
	 * Beinhaltet die Daten
	 *
	 * @var array $data
	 */
	public $data = array();

	/**
	 * Verhindert das Seiten mehrmals ausgelesen werden
	 *
	 * @var array $analysed
	 */
	private $analysed = array(0, 0, 0);

	/**
	 * Beinhaltet das zu analysierende Material
	 * Variable um Daten zwischenspeichern zu können
	 * Variable um Daten zwischenspeichern zu können
	 * Varible für Schleifen
	 *
	 * @var string  $content
	 * @var string  $cache
	 * @var string  $cache2
	 * @var integer $i
	 */
	private $content,
	$cache,
	$cache2,
	$i;

	//Erstelle neues Objekt und fülle Variablen mit Benutzerinformationen
	public function __construct($name, $server, $getall) {
		$this->data["NAME"] = $name;
		$this->data["SERVER"] = $server;
		if ($getall === true) {
			$this->getProfile();
			$this->getHP();
			$this->getGeschlecht();
		}
	}

	public function getProfile() {
		if ($this->analysed[0] == 0) {
			$this->analysed[0] = 1;
			//Lade Profilseite herunter
			$this->content = @file_get_contents('http://www.freggers.'.$this->data["SERVER"].'/sidebar/profile/user/'.$this->data["NAME"].'?v='.date('YmdHis'));
			if ($this->content) {
				//Bereinige Webseitencode von Tabs und umbr�chen
				$this->content = preg_replace("/\s+/", " ", $this->content);

				//FreggersID
				preg_match("/javascript:zoomFregger\(\'(.*?)\'\)\">/", $this->content, $this->cache);
				$this->data["ID"] = intval($this->cache[1]);

				//FreggersName
				preg_match("/flashvars.username=\"(.*?)\";/", $this->content, $this->cache);
				$this->data["NAME"] = $this->cache[1];

				//Alter
				preg_match("/".$this->data["Name"]." \((.*?)\)<\/div>/", $this->content, $this->cache);
				$this->data["ALTER"] = intval($this->cache[1]);

				//Ort
				preg_match("/<div style=\"padding-bottom: 10px;\">(.*?)<\/div> \((.*?)\) <div style=\"position: relative\">/", $this->content, $this->cache);
				$this->data["ORT"] = $this->cache[2];

				//Online
				if (strpos($this->data["Ort"], "<i>") !== false) {
					$this->data["ONLINE"] = false;
				} else {
					$this->data["ONLINE"] = true;
				}

				//Gesamte bisher erhaltene Freekr�ten
				preg_match('/<div id="profile-toads" class="profile-info"> (.*?) <div/', $this->content, $this->cache);
				$this->data["FREETOADS"] = intval($this->cache[1]);

				//Online Stunden
				preg_match('/<div id="profile-online-time" class="profile-info"> (.*?) <div/', $this->content, $this->cache);
				$this->data["ONLINESTUNDEN"] = intval($this->cache[1]);

				//Inventarwert
				preg_match('/<div id="profile-inv-value" class="profile-info"> (.*?) <div/', $this->content, $this->cache);
				$this->data["INVENTARWERT"] = intval($this->cache[1]);

				//Webseite
				preg_match('/" target="_blank">(.*?)<\/a><\/div> <\/div> <\/div> <h1>Abzeichen/', $this->content, $this->cache);
				$this->data["WEBSEITE"] = $this->cache[1];

				//Freunde
				preg_match_all("|class=\"friendlist-image\" name=\"(.*)\"|siU", $this->content, $this->cache);
				$this->data["FREUNDE"] = count($this->cache[1]);

				//Freunde online
				preg_match_all('|<span style="color: Black; white-space: nowrap;">(.*)<|siU', $this->content, $this->cache);
				$this->data["FREUNDEONLINE"] = count($this->cache[1]);

				//Serverop
				if (strpos($this->content, "serverop-marker login-string") !== false) {
					$this->data["SERVEROP"] = true;
				} else {
					$this->data["SERVEROP"] = false;
				}

				//Admin
				if (strpos($this->content, "admin-marker login-string") !== false) {
					$this->data["ADMIN"] = true;
				} else {
					$this->data["ADMIN"] = false;
				}

				//Wohnungsgr��e
				preg_match("/roomgui=\"plattenbau.eigenheim(.*?)\";/", $this->content, $this->cache);
				$this->data["ZIMMERANZAHL"] = intval($this->cache[1]);

				//Teamname
				preg_match("/class=\"team-header\">(.*?)<\/div>/", $this->content, $this->cache);
				$this->data["TEAMNAME"] = $this->cache[1];

				//Team Mitglieder
				preg_match("/<div class=\"stats\">(.*?)<br>/", $this->content, $this->cache);
				$this->data["TEAMMITGLIEDER"] = intval($this->cache[1]);

				//Faun Level
				preg_match("/<span class=\"level\">(.*?)<\/span>/", $this->content, $this->cache);
				preg_match('/(\d+)/', $this->cache[1], $this->cache);
				$this->data["FAUNLEVEL"] = intval($this->cache[1]);
			} else {
				//Content konnte nicht runter geladen werden
			}
		} else {
			//Profil wurde schon analysiert
		}
	}

	public function getHP() {
		if ($this->analysed[1] == 0) {
			$this->analysed[1] = 1;
			$this->content = @file_get_contents('http://www.freggers.'.$this->data["SERVER"].'/hp/'.$this->data["NAME"]);
			if ($this->content) {

				//Alle Abzeichen
				$this->content = preg_replace("/\s+/", " ", $this->content);
				//preg_match_all('|<div class="badge-name">(.*)</div>|siU',$this->content,$this->cache); //Erm�glicht das auslesen der Namen !!!!!
				preg_match_all('|class="ba-badge-container" style="float: left;">(.*)</div> </div> </div>|siU', $this->content, $this->cache); //Version ohne Namen aber daf�r fertiige Abzecihen Z�hler
				$this->data["ABZEICHENGESAMT"] = count($this->cache[1]);

				//Fertige Abzeichen auslesen
				$this->data["ABZEICHENFERTIG"] = 0;
				for ($this->i = 0; $this->data["ABZEICHENGESAMT"] > $this->i; $this->i++) {
					/* echo '<br><br>'.$this->cache[1][$this->i].'<br>';
					  echo "\n\n"; */
					if (strpos($this->cache[1][$this->i], "ba-requirement ba-requirement-achieved ba-requirement-best-achieved") !== false) {
						preg_match_all('|<div class="ba-requirement (.*)">|siU', $this->cache[1][$this->i], $this->cache2);
						if ($this->cache2[1][count($this->cache2[1]) - 1] == 'ba-requirement-achieved ba-requirement-best-achieved') {
							$this->data["ABZEICHENFERTIG"]++;
						}
					} else {
						if (strpos($this->cache[1][$this->i], 'ba-requirement ba-requirement-achieved') !== false) {
							$this->data["ABZEICHENFERTIG"]++;
						}
					}
				}
			} else {
				//Konnte HP nicht runterladen
			}
		} else {
			//HP wurde schonmal analysiert
		}
	}

	public function getGeschlecht() {
		//Geschlecht
		if ($this->analysed[2] == 0) {

			$this->analysed[2] = 1;
			$this->content = @imagecreatefromgif('http://www.freggers.'.$this->data['SERVER'].'/img/large/'.$this->data['ID'].'/3_idle_0/fregger.gif?i=978');
			//echo 'http://www.freggers.'.$this->data["SERVER"].'/img/large/'.$this->data["ID"].'/1_idle_0/fregger.png?i=2;i=13;i=33;i=991;'; // use?

			if ($this->content) {
				$this->cache = imagecolorat($this->content, 53, 163);

				$this->data['GESCHLECHT'] = '2'; //Weiblich
				if ($this->cache == 0) {
					$this->data['GESCHLECHT'] = '3'; //männlich
				}
			} else {
				$this->data['GESCHLECHT'] = 'error'; // image download failed
			}

			imagedestroy($this->content); // free memory
		} else {
			//Geschlecht wurde schonmal analysiert
		}
	}

}

//$neuerFregger = new Freggeranalyser("freggern", "de", true);
//print_r($neuerFregger->data);

?>