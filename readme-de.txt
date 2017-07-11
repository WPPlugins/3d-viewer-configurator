Kurzübersicht
-------------

Der 3D Produkt Viewer für Wordpress ermöglicht die 3D-Anzeige von Produktkonfigurationen
im Frontend. Gleichzeitig bietet er im Backend die Möglichkeit, die Konfigurationen
anzulegen und die entsprechenden Bilder hochzuladen.

Für den Upload der Bilder (Wichtig: zuerst eine Konfiguration anlegen!) gibt es mehrere 
Möglichkeiten:
- Variante 1: Upload eines Zip-Archivs, welches die komplette Ordnerstruktur für mehrere
              Konfigurationen enthält (ein Beispiel der erforderlichen Struktur ist im 
              Backend anzeigbar)
- Variante 2: Erstellung neuer Konfigurationen durch Angabe eines Namens im ersten Schritt.
              Im nächsten Schritt können dann die Produktbilder entsprechend den Bereichen
              (360°/Zoom/Thumbnail) hochgeladen werden. Der Upload der Bilder kann wahlweise
              einzeln, in Gruppen (mehrere Bilder auf einmal auswählen) oder als Zip-Datei
              mit Bildern erfolgen.
- Variante 3: Upload der Daten per FTP.
              Nach Erstellung der Konfiguration befindet sich im Wordpress-Installationsverzeichnis
              unter dem folgenden Pfad 
                 "wp-content/plugins/wp_vtpkonfigurator_<version>/data/"
              ein Verzeichnis mit dem Namen des angelegten 3D-Konfigurators.
              In dieses Verzeichnis können Sie Ihre 3D-Konfigurationen gemäß der folgenden Dateistruktur 
              hochladen:
              configuration1/ (Verzeichnis der ersten 3D-Konfiguration)
              -- view/ (Bilder der ersten 3D-Konfiguration)
				  -- -- zoom/ (Zoom-Bilder der ersten 3D-Konfiguration)
              -- -- -- img0.jpg
              -- -- -- img1.jpg
              -- -- img0.jpg
              -- -- img1.jpg
              -- thumb.jpg (Thumbnail für Auswahl der Konfiguration)
              Nach Übertragung der Daten und einem Neuladen der Browserseite im Backend werden
              die 3D-Konfigurationen direkt sichtbar.

WICHTIG: Die Gesamtgröße der hochladbaren Datei(en) richtet sich für die Varianten 1 und 2 nach den 
         im Abschnitt "Voraussetzungen" beschriebenen serverseitigen Parametern.


Installation
------------

Die Installation des Plugins erfolgt Wordpress-typisch durch einen simplen Upload der 
Plugin-Zip-Datei. Zum Upload-Formlar gelangen Sie durch Klick auf den Menüpunkt "Plugins", 
danach wählen Sie "Add New" und dann "Upload".


Voraussetzungen
---------------

Die Funktionalität des Plugins hängt von einigen Einstellungen der serverseitigen
PHP-Konfiguration ab. Für den problemlosen Betrieb müssen die folgenden Einstellungen
in der entsprechenden PHP Konfigurationsdatei (üblicherweise eine Datei namens php.ini)
eventuell angepasst werden. Bitte wenden Sie sich in diesem Fall an Ihren Provider oder
den entsprechenden technischen Ansprechpartner.

- Parameter "post_max_size"
  Bestimmt die maximal erlaubte Dateigröße für Dateiuploads über Formulare mittels POST
  (Der kleinere Wert post_max_size/upload_max_filesize ist ausschlaggebend)

- Parameter "upload_max_filesize"
  Bestimmt die maximal erlaube Dateigröße für Uploads allgemein
  (Der kleinere Wert post_max_size/upload_max_filesize ist ausschlaggebend)

- Parameter "max_file_uploads"
  Bestimmt die maximale Anzahl einzelner Dateien, die innerhalb eines Upload-Vorgangs
  hochgeladen werden können
  
- Parameter "max_execution_time"
  Zeitlimit in Sekunden, die ein Skript für das Verarbeiten hochgeladener Daten mittels POST/GET
  benötigen darf. Wird diese überschritten, wird die Ausführung abgebrochen.
  Nach dem Upload werden die Dateien des Archivs entpackt und an die richtige Stelle verschoben.
  Ist dieser Wert zu gering eingestellt, kann der eben genannte Vorgang nicht vollständig
  ausgeführt werden.
  

Weitere Bemerkungen
-------------------

- Bei Verwendung der "Variante 1" werden alle Verzeichnisse der obersten Ebene als
  Konfigurationen interpretiert. Einzigste Ausnahme bildet das Verzeichnis "__MACOSX",
  welches typischerweise automatisch auf Apple-Rechnern angelegt wird.
  Weiterhin werden alle Dateien ignoriert, die mit einem Punkt (.) beginnen, z.B. .DS_STORE

- In "Variante 2" können gleichzeitig mehrere Bilder wie auch eine Zip-Datei mit Bildern
  hochgeladen werden. Wird eine Zip-Datei ausgewählt, werden alle Bilder zum aktuellen Bereich
  (360°/Zoom) hinzugefügt, unabhängig davon, wie die Ordnerstruktur innerhalb der Zip-Datei aussieht.
  Befindet sich mehrere Bilder mit identischen Dateinamen in verschiedenen Ordnern in der Zip-Datei,
  überschreibt die zuletzt gelesene Datei zuvor bearbeitete Dateien (Reihenfolge alphabetisch und
  rekursiv).