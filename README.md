# Nordwerk Theme-Basis

Contao-Bundle für Contao 5.7.12 oder neuer innerhalb 5.7. Installation über Composer oder die versionierte Manager-ZIP.

Gemeinsame Layoutvorlagen, lokale OFL-Schriften, Gestaltung unter **Layout → Themes → Theme bearbeiten → Gestaltung** und sichere Demo-Import-Mechanik für weitere Nordwerk-Themes. Das Paket verlangt keinen Mini-Shop.

Ein `DemoProvider` liefert `key()`, `title()`, `assertCanImport()` und `apply()`. `DemoImporter` schützt den Import durch eine Datenbank-Sperre und Transaktion. Ohne ausdrücklich bestätigte Ersetzung bleibt ein vorhandener Import unverändert.

Farben und Schrift wählen Sie im Assistenten; weitergehende Anpassungen sind über `--nw-*`-CSS-Variablen und Contao Template Studio möglich. Dokumentation im Monorepo: `docs/theming.md`. Größere Anpassungen: [Studio Nordwerk](https://www.nordwerk.studio/contao#entwicklung).

Code: MIT. Schriftlizenzen im Basispaket unter `public/fonts/`. Bildherkunft im Shop-Paket unter `demo/IMAGE-SOURCES.md`.
