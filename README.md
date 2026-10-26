# Nordwerk Theme-Basis

Contao-Bundle für Contao 5.7.12 oder neuer innerhalb 5.7. Installation über Composer oder die versionierte Manager-ZIP.

Gemeinsame Layoutvorlagen, lokale OFL-Schriften, Gestaltung unter **Layout → Themes → Theme bearbeiten → Gestaltung** und sichere Demo-Import-Mechanik für weitere Nordwerk-Themes. Das Paket verlangt keinen Mini-Shop.

- Für jedes Seitenlayout eines Nordwerk-Themes lädt die Basis die Stylesheets des Themes (mit Versionsnummer, vor den „Externen Stylesheets“ des Layouts) und die Farben und Schriften aus dem Theme-Formular, unabhängig von den Modulen des Layouts.
- **Layout → Theme einrichten** legt die Demo-Seiten der installierten Themes an und zeigt die Gestaltung. Das Modul hat ein eigenes Recht in den Benutzergruppen.
- Das Twig-Layout `page/layout/nw_theme` (Layout-Typ „Modern“) bietet die Slots `header`, `main` und `footer`.
- Texte auf Deutsch und Englisch; Frontend-Texte lassen sich in `translations/messages.<sprache>.yaml` des Projekts überschreiben (`nw_theme.*`).

Ein Theme-Paket registriert einen Dienst mit `Nordwerk\ThemeBaseBundle\Theme\ThemePackage`. Sein `DemoProvider` liefert `key()`, `title()`, `assertCanImport()` und `apply()`, optional `DemoInventory::records()`. `DemoImporter` schützt den Import durch eine Datenbank-Sperre und Transaktion, versioniert die importierten Datensätze und invalidiert ihre Cache-Tags. Ohne ausdrücklich bestätigte Ersetzung bleibt ein vorhandener Import unverändert.

Farben und Schrift wählen Sie im Theme-Formular; weitergehende Anpassungen sind über `--nw-*`-CSS-Variablen und Contao Template Studio möglich. Dokumentation im Monorepo: `docs/theming.md`. Größere Anpassungen: [Studio Nordwerk](https://www.nordwerk.studio/contao#entwicklung).

Code: MIT. Schriftlizenzen im Basispaket unter `public/fonts/`. Bildherkunft im Shop-Paket unter `demo/IMAGE-SOURCES.md`.
