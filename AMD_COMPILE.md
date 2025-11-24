# AMD Module Kompilierung für ButtonsX

## Wichtig!

Die JavaScript-Module in `amd/src/` müssen kompiliert werden, bevor sie im Browser funktionieren.

## Option 1: Automatische Kompilierung (Empfohlen)

Moodle kompiliert AMD-Module automatisch wenn:
1. Developer-Modus aktiviert ist
2. Theme-Cache deaktiviert ist

### Developer-Modus aktivieren:

```php
// In config.php hinzufügen:
$CFG->cachejs = false;
$CFG->themedesignermode = true;
```

**Warnung:** Nur in Entwicklungsumgebungen verwenden!

## Option 2: Manuell mit Grunt (Empfohlen für Produktion)

### Voraussetzungen:

```bash
# Node.js und npm installiert?
node --version
npm --version

# Falls nicht, installiere Node.js von nodejs.org
```

### Grunt Setup:

```bash
# Im Moodle-Root-Verzeichnis
cd /path/to/moodle

# Dependencies installieren (einmalig)
npm install

# Grunt global installieren (falls noch nicht vorhanden)
npm install -g grunt-cli
```

### AMD Module kompilieren:

```bash
# Alle AMD Module kompilieren
grunt amd

# Nur ButtonsX Module kompilieren (schneller)
grunt amd --root=course/format/buttonsx

# Watch-Modus (automatische Kompilierung bei Änderungen)
grunt watch
```

### Ergebnis überprüfen:

```bash
# Diese Dateien sollten existieren:
ls -la course/format/buttonsx/amd/build/

# Erwartete Dateien:
# mutations.min.js
# mutations.min.js.map
# section.min.js
# section.min.js.map
```

## Option 3: Purge Caches (Notfall-Lösung)

Falls AMD-Module nicht geladen werden:

```bash
# CLI
php admin/cli/purge_caches.php

# Oder via Web-Interface:
# Site administration > Development > Purge all caches
```

## Troubleshooting

### Problem: "Cannot find module 'format_buttonsx/section'"

**Lösung:**
```bash
# 1. Überprüfe ob Source-Dateien existieren
ls -la course/format/buttonsx/amd/src/

# 2. Kompiliere AMD
grunt amd --root=course/format/buttonsx

# 3. Überprüfe Build-Dateien
ls -la course/format/buttonsx/amd/build/

# 4. Purge Caches
php admin/cli/purge_caches.php

# 5. Browser-Cache leeren (Strg+Shift+Del)
```

### Problem: Grunt Fehler "Cannot find module"

**Lösung:**
```bash
# Node modules neu installieren
cd /path/to/moodle
rm -rf node_modules
npm install

# Grunt neu installieren
npm install -g grunt-cli

# Erneut versuchen
grunt amd
```

### Problem: JavaScript-Syntax-Fehler

**Lösung:**
```bash
# Check Syntax
node -c course/format/buttonsx/amd/src/section.js
node -c course/format/buttonsx/amd/src/mutations.js

# Falls Fehler, korrigiere und kompiliere neu
grunt amd --root=course/format/buttonsx
```

## Entwicklung

### Workflow für JavaScript-Änderungen:

1. **Bearbeite** `amd/src/*.js`
2. **Kompiliere** mit `grunt amd`
3. **Purge** Caches
4. **Teste** im Browser
5. **Commit** sowohl src als auch build Dateien

### Watch-Modus nutzen:

```bash
# Terminal offen lassen
grunt watch

# In anderem Terminal arbeiten
# Änderungen werden automatisch kompiliert
```

### JavaScript-Linting:

```bash
# ESLint prüfen
npm run eslint -- course/format/buttonsx/amd/src/*.js

# Auto-Fix
npm run eslint:fix -- course/format/buttonsx/amd/src/*.js
```

## Produktiv-Deployment

### Vor dem Deployment:

```bash
# 1. AMD kompilieren
grunt amd

# 2. Minified-Dateien überprüfen
ls -la course/format/buttonsx/amd/build/*.min.js

# 3. Source-Maps erstellt?
ls -la course/format/buttonsx/amd/build/*.map

# 4. Git commit (inkl. build-Dateien!)
git add course/format/buttonsx/amd/
git commit -m "AMD modules compiled"
```

### Nach dem Deployment:

```bash
# Auf Produktiv-Server
php admin/cli/purge_caches.php

# Developer-Modus deaktivieren
# In config.php entfernen oder auskommentieren:
# $CFG->cachejs = false;
# $CFG->themedesignermode = true;
```

## Quick Reference

### Häufige Befehle:

```bash
# Alle AMD kompilieren
grunt amd

# ButtonsX spezifisch
grunt amd --root=course/format/buttonsx

# Watch-Modus
grunt watch

# Caches purgen
php admin/cli/purge_caches.php

# Syntax Check
node -c amd/src/file.js
```

### Wichtige Pfade:

```
Source:  course/format/buttonsx/amd/src/*.js
Build:   course/format/buttonsx/amd/build/*.min.js
Maps:    course/format/buttonsx/amd/build/*.min.js.map
```

## Hinweise

1. **Immer kompilieren** vor dem Testen
2. **Build-Dateien committen** (nicht nur Source)
3. **Developer-Modus** nur in Entwicklung
4. **Caches purgen** nach Änderungen
5. **Browser-Cache** auch leeren

## Support

Bei Problemen:
1. Überprüfe Node.js Version (>= 14)
2. Überprüfe npm Version (>= 6)
3. Reinstalliere Dependencies
4. Checke Grunt-Log für Fehler
5. Erstelle Issue mit Details

---

**Hinweis:** Diese Datei ist Teil von ButtonsX 4.4 für Moodle 4.4+
