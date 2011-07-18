<?php

/**
 * Funktion zum Analysieren von einem Fregger(s Profil).
 *
 * @author Freggern / Kurtextrem
 * @license CC BY-SA <http://creativecommons.org/licenses/by-sa/3.0/>
 * @see <https://github.com/freggern/Freggers-Analyser-de-> für mehr Details.
 */

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

	/**
	 * Erstelle neues Objekt und fülle Variablen mit Benutzerinformationen
	 *
	 * @param string  $name
	 * @param string  $server
	 * @param boolean $getall
	 */
	public function __construct($name, $server, $getall = false) {
		$this->data['NAME'] = $name;
		$this->data['SERVER'] = $server;
		if ($getall) {
			$this->getProfile();
			$this->getHP();
			$this->getGeschlecht();
		}
	}

	/**
	 * Fügt FreggerID,
	 * Freggerasname,
	 * Alter,
	 * Ort,
	 * Online (boolean),
	 * erhaltene Freikröten,
	 * Online Stunden,
	 * Inventarwert,
	 * Website,
	 * Freunde,
	 * Serverop (boolean),
	 * Admin (boolean),
	 * Wohnungsgröße (integer),
	 * Teamname,
	 * Team Mitglieder (integer),
	 * Faun Level (integer)
	 * zu $this->data hinzu.
	 */
	public function getProfile() {
		if ($this->analysed[0] == 0) {
			$this->analysed[0] = 1;
			// Lade Profilseite herunter
			$this->content = @file_get_contents('http://www.freggers.'.$this->data['SERVER'].'/sidebar/profile/user/'.$this->data['NAME'].'?v='.date('YmdHis'));
			if ($this->content) {
				// Bereinige Webseitencode von Tabs und umbrüchen
				$this->content = preg_replace('/\s+/', ' ', $this->content);

				// FreggerID
				preg_match("/javascript:zoomFregger\(\'(.*?)\'\)\">/", $this->content, $this->cache);
				$this->data['ID'] = intval($this->cache[1]);

				// Freggersname
				preg_match('/flashvars.username="(.*?)";/', $this->content, $this->cache);
				$this->data['NAME'] = $this->cache[1];

				// Alter
				preg_match('/'.$this->data['NAME'].' \((.*?)\)<\/div>/', $this->content, $this->cache);
				$this->data['ALTER'] = intval($this->cache[1]);

				// Ort
				preg_match('/<div style="padding-bottom: 10px;">(.*?)<\/div> \((.*?)\) <div style="position: relative">/', $this->content, $this->cache);
				$this->data['ORT'] = $this->cache[2];

				// Online
				if (strpos($this->data['Ort'], '<i>') !== false) {
					$this->data['ONLINE'] = false;
				} else {
					$this->data['ONLINE'] = true;
				}

				// Gesamte bisher erhaltene Freikröten
				preg_match('/<div id="profile-toads" class="profile-info"> (.*?) <div/', $this->content, $this->cache);
				$this->data['FREETOADS'] = intval($this->cache[1]);

				// Online Stunden
				preg_match('/<div id="profile-online-time" class="profile-info"> (.*?) <div/', $this->content, $this->cache);
				$this->data['ONLINESTUNDEN'] = intval($this->cache[1]);

				// Inventarwert
				preg_match('/<div id="profile-inv-value" class="profile-info"> (.*?) <div/', $this->content, $this->cache);
				$this->data['INVENTARWERT'] = intval($this->cache[1]);

				// Webseite
				preg_match('/" target="_blank">(.*?)<\/a><\/div> <\/div> <\/div> <h1>Abzeichen/', $this->content, $this->cache);
				$this->data['WEBSEITE'] = $this->cache[1];

				// Freunde
				preg_match_all('|class="friendlist-image" name="(.*)"|siU', $this->content, $this->cache);
				$this->data['FREUNDE'] = count($this->cache[1]);

				// Freunde online
				preg_match_all('|<span style="color: Black; white-space: nowrap;">(.*)<|siU', $this->content, $this->cache);
				$this->data['FREUNDEONLINE'] = count($this->cache[1]);

				//Serverop
				if (strpos($this->content, 'serverop-marker login-string') !== false) {
					$this->data['SERVEROP'] = true;
				} else {
					$this->data['SERVEROP'] = false;
				}

				//Admin
				if (strpos($this->content, 'admin-marker login-string') !== false) {
					$this->data['ADMIN'] = true;
				} else {
					$this->data['ADMIN'] = false;
				}

				//Wohnungsgröße
				preg_match('/roomgui="plattenbau.eigenheim(.*?)";/', $this->content, $this->cache);
				$this->data['ZIMMERANZAHL'] = intval($this->cache[1]);

				//Teamname
				preg_match('/class="team-header">(.*?)<\/div>/', $this->content, $this->cache);
				$this->data['TEAMNAME'] = $this->cache[1];

				//Team Mitglieder
				preg_match('/<div class="stats">(.*?)<br>/', $this->content, $this->cache);
				$this->data['TEAMMITGLIEDER'] = intval($this->cache[1]);

				//Faun Level
				preg_match('/<span class="level">(.*?)<\/span>/', $this->content, $this->cache);
				preg_match('/(\d+)/', $this->cache[1], $this->cache);
				$this->data['FAUNLEVEL'] = intval($this->cache[1]);
			} else {
				//Content konnte nicht runter geladen werden
			}
		} else {
			//Profil wurde schon analysiert
		}
	}

	/**
	 * Fügt die Homepage des Freggers zu $this->data hinzu.
	 */
	public function getHP() {
		if ($this->analysed[1] == 0) {
			$this->analysed[1] = 1;
			$this->content = @file_get_contents('http://www.freggers.'.$this->data['SERVER'].'/hp/'.$this->data['NAME']);
			if ($this->content) {

				//Alle Abzeichen
				$this->content = preg_replace("/\s+/", " ", $this->content);
				//preg_match_all('|<div class="badge-name">(.*)</div>|siU',$this->content,$this->cache); //Erm�glicht das auslesen der Namen !!!!!
				preg_match_all('|class="ba-badge-container" style="float: left;">(.*)</div> </div> </div>|siU', $this->content, $this->cache); //Version ohne Namen aber daf�r fertiige Abzecihen Z�hler
				$this->data['ABZEICHENGESAMT'] = count($this->cache[1]);

				//Fertige Abzeichen auslesen
				$this->data['ABZEICHENFERTIG'] = 0;
				for ($this->i = 0; $this->data['ABZEICHENGESAMT'] > $this->i; $this->i++) {
					/* echo '<br><br>'.$this->cache[1][$this->i].'<br>';
					  echo "\n\n"; */
					if (strpos($this->cache[1][$this->i], 'ba-requirement ba-requirement-achieved ba-requirement-best-achieved') !== false) {
						preg_match_all('|<div class="ba-requirement (.*)">|siU', $this->cache[1][$this->i], $this->cache2);
						if ($this->cache2[1][count($this->cache2[1]) - 1] == 'ba-requirement-achieved ba-requirement-best-achieved') {
							$this->data['ABZEICHENFERTIG']++;
						}
					} else {
						if (strpos($this->cache[1][$this->i], 'ba-requirement ba-requirement-achieved') !== false) {
							$this->data['ABZEICHENFERTIG']++;
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

	/**
	 * Fügt das Geschlecht des Freggers zu $this->data hinzu.
	 */
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