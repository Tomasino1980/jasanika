# Copilot Workflow

## Princip

Copilot Agent je programátor.

Není správce projektu.

Není správce Git historie.

---

## Definice Milníku

Každý Milník musí být jasně definován před zahájením implementace.

Milník musí obsahovat:

* cíl
* seznam úkolů
* očekávaný výsledek
* omezení
* kritéria dokončení

Implementace nesmí začít, dokud není Milník schválen.

Pokud během implementace vznikne potřeba rozšíření funkcionality, musí být tato změna schválena a zařazena do nového Milníku.

---

## Pravidlo rozsahu

Copilot Agent plní pouze zadané úkoly.

Nevytváří:

* dodatečné funkce
* nové moduly
* nové závislosti
* změny architektury

pokud nejsou výslovně součástí schváleného Milníku.

Každý Milník má mít jednoznačný rozsah a jasný výstup.

Agent nesmí implementovat funkcionalitu z budoucích Milníků.

Pokud je funkcionalita plánována v pozdějším Milníku roadmapy, musí být implementována až v okamžiku schválení daného Milníku.

---

## Odpovědnosti

### Copilot Agent

* vytváří kód
* upravuje soubory
* navrhuje řešení

Před zahájením práce musí načíst a respektovat:

* docs/project-rules.md
* docs/folder-structure.md
* docs/design-system.md
* docs/typography.md
* docs/roadmap.md
* docs/copilot-workflow.md

### Vývojář

* zadává Milníky
* kontroluje výsledek
* testuje
* vytváří commity
* spravuje GitHub

---

## Workflow

Zadání Milníku

↓

Schválení Milníku

↓

Načtení dokumentace

↓

Implementace Agentem

↓

Kontrola

↓

Testování

↓

Schválení

↓

Commit Milníku

↓

GitHub

---

## Chybná implementace

Pokud Agent vytvoří nevyhovující řešení:

1. Session se ukončí.
2. Worktree se odstraní.
3. Vytvoří se nová Session.
4. Milník se zadá znovu.

---

## Git Workflow

Copilot Agent neprovádí Git operace.

Copilot Agent:

* nevytváří commity
* nevytváří větve
* nevytváří worktree
* nevytváří pull requesty
* neprovádí merge
* neprovádí rebase
* neprovádí push
* nemění Git konfiguraci

Veškeré Git operace provádí výhradně vývojář.

---

## Historie projektu

Historie projektu obsahuje pouze schválené Milníky.

Příklad:

M0 - INITIAL

M1 - Theme Skeleton

M2 - Layout Foundation

M3 - Administration Foundation

M4 - Menu System

...

V historii nesmí vznikat pomocné commity, experimentální větve ani technické Git operace související s prací Agenta.

---

## Důležité pravidlo

Agent nikdy nevytváří oficiální historii projektu.

Historii projektu vytváří pouze vývojář.

## Theme Version

At the end of every completed milestone, update the Version field in style.css.

Examples:

M0 → Version: 0.0.0
M1 → Version: 0.1.0
M2 → Version: 0.2.0
M3 → Version: 0.3.0
M4 → Version: 0.4.0
M5 → Version: 0.5.0
M6 → Version: 0.6.0
M7 → Version: 0.7.0
M8 → Version: 0.8.0
M9 → Version: 0.9.0
M10 → Version: 0.10.0
M11 → Version: 0.11.0
M12 → Version: 0.12.0

etc...

The version number must always match the latest completed milestone.

## Git Workflow Rules

For every milestone:

1. Create all changes only inside the current agent branch/worktree.
2. Never modify the main branch directly.
3. Commit all completed work into the current agent branch.
4. Do not ask the user to manually copy files between branches.
5. Do not create additional branches unless explicitly requested.
6. The implementation branch must always start from the current main branch state.
7. At milestone completion, provide a summary of:

   * Modified files
   * Created files
   * Acceptance criteria
   * Suggested commit message

Main branch updates are performed only after user review and approval.

Before reporting milestone completion:

git status

Po dokončení proveď commit s těmito parametry:
    git commit -m "M<milestone> - <milestone name>"

Only after successful commit may the milestone be considered completed.
