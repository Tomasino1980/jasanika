# Struktura projektu

jasanika-theme/

style.css
functions.php
index.php

header.php
footer.php

front-page.php
page.php
single.php
archive.php
404.php

screenshot.png

assets/

```
css/

    base/
        variables.css
        reset.css
        typography.css

    layout/
        header.css
        footer.css
        grid.css
        containers.css

    components/
        buttons.css
        cards.css
        navigation.css
        forms.css

    pages/
        homepage.css

js/

    navigation.js
    homepage.js

images/
```

inc/

```
enqueue.php
menus.php
theme-support.php
cleanup.php
```

template-parts/

```
navigation/
hero/
cards/
sections/
```

docs/

```
project-rules.md
folder-structure.md
roadmap.md
design-system.md
typography.md
copilot-workflow.md
```

---

## Zásady

### CSS

* jedna oblast = jeden soubor
* žádný gigantický style.css

### JavaScript

* jedna funkce = jeden soubor

### Template Parts

* znovupoužitelné komponenty
* bez duplicitního HTML

### Inc

* pouze WordPress logika
* žádný HTML výstup

---

# Git Workflow

## MAIN

Adresář:

themes/jasanika/

Obsahuje schválenou a stabilní verzi projektu.

MAIN musí být vždy funkční.

---

## Copilot Agent Worktree

Adresář:

themes/jasanika.worktrees/

Každá Session může mít vlastní Worktree.

Worktree je pouze pracovní prostor.

Není součástí produkční historie projektu.

Po dokončení nebo zrušení Session může být Worktree odstraněna.

---

## Postup práce

1. Zadání Milníku.
2. Agent vytvoří změny.
3. Změny se zkontrolují.
4. Změny se otestují.
5. Po schválení se převezmou do MAIN.
6. MAIN se odešle na GitHub.

---

## Důležité pravidlo

Historie MAIN musí být přehledná.

Příklad:

M0 - INITIAL

M1 - Theme Skeleton

M2 - Layout Foundation

M3 - Administration Foundation

M4 - Menu System

M5 - Hero Section

...

Nepoužívat:

* experimentální commity
* pracovní commity
* technické commity Copilot Agenta
* merge commit chaos
