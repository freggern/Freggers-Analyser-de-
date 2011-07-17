FW_API_FREGGERSANALYSER
=======================
Beschreibung
------------
Ermöglicht das auslesen bestimmter Informationen vom Freggers Server

Version
--------
0.1

Author
------
Bernhard Eisele / GitHub Community

License
-------
CC BY-SA 3.0 (http://creativecommons.org/licenses/by-sa/3.0/de/)
<br>
**ACHTUNG ZUM AUSLESEN VON DATEN BRAUCHT IHR DIE ERLAUBNIS VON DEN MACHERN VON FREGGERS**<br>
=> http://www.freggers.de/help/imprint
<br>
Versionshistory
---------------
 * 0.1 RC1
	 * Auslesen aller wichtigen Daten
 * 0.1 RC2
	 * Auslesen der Daten von Garten des Fauns
 * 0.1
	 * Nun Objektorentiert.
	 * Fix Abzeichen Gesamt + Abzeichen fertig
     * Entfernen unötiger Variablen
 * 0.2
	 * Fixe Geschlechtererkennung
	 * Kommentierung verbessert

Funktionsbeschreibung
---------------------

`$neuerFregger = new FW_API_FREGGERSANALYSER (Sting mit Freggersname, String mit Freggersserver, true or false);`

`Freggersname`   => Hier wird angegeben welcher Freggersname analysiert werden soll
`Freggersserver` => Hier wird angegeben auf welchem Server sich der Freggers besfindet (Nur Domainendung de oder com angeben)
`true - false`   => Hier wird angegeben ob alles analysiert wird oder man kann die einzelnen Schritte auch per Hand durchgehen
					`$neuerFregger->getProfile();
					$neuerFregger->getHP();
					$neuerFregger->getGeschlecht(); //Benötigt getProfile();`

Rückgabe:
Wenn der Freggers nicht existiert werden leere Felder zurückgegeben

Zurückgabe bei einem existierenden Freggers mit Beispielwerten:
`data(
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
`