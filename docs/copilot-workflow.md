# Copilot Workflow

## Princip

Copilot Agent je programátor.

Není správce projektu.

Není správce Git historie.

Není release manager.

---

## Priority Rule

Pokud jsou instrukce v tomto dokumentu v konfliktu s výchozím chováním Copilot Agenta, platí instrukce uvedené v tomto dokumentu.

Nedodržení pravidel workflow znamená, že Milník není dokončen.

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

## Povinné dokumenty

Před zahájením práce musí Agent načíst a respektovat:

* docs/project-rules.md
* docs/folder-structure.md
* docs/design-system.md
* docs/typography.md
* docs/roadmap.md
* docs/copilot-workflow.md

Implementace nesmí začít před načtením těchto dokumentů.

---

## Odpovědnosti

### Copilot Agent

* vytváří kód
* upravuje soubory
* navrhuje řešení
* implementuje schválený Milník
* vytváří commit ve své pracovní větvi/worktree

### Vývojář

* zadává Milníky
* schvaluje Milníky
* kontroluje výsledek
* testuje implementaci
* provádí merge do main
* provádí push
* spravuje GitHub
* spravuje historii projektu

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

Commit Milníku Agentem

↓

Schválení vývojářem

↓

Merge do main

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

Copilot Agent pracuje pouze ve své pracovní větvi nebo worktree.

Copilot Agent:

* vytváří změny pouze ve své pracovní větvi/worktree
* nikdy neupravuje main branch přímo
* vytváří commit Milníku ve své pracovní větvi/worktree
* neprovádí merge
* neprovádí rebase
* neprovádí push
* nemění Git konfiguraci

Vývojář:

* kontroluje implementaci
* testuje implementaci
* schvaluje Milník
* provádí merge do main
* provádí push
* spravuje GitHub
* spravuje historii projektu
* maže pracovní větve a worktree

---

## Historie projektu

Historie projektu obsahuje pouze schválené Milníky.

Příklad:

M0 - INITIAL

M1 - Theme Skeleton

M2 - Layout Foundation

M3 - Administration Foundation

M4 - Navigation System

...

Checkpointy, interní artefakty Agenta a pracovní commity nejsou součástí oficiální historie projektu.

Do oficiální historie projektu se dostávají pouze schválené Milníky po merge do main.

---

## Theme Version

Na konci každého dokončeného Milníku musí být aktualizována položka Version v souboru style.css.

IMPORTANT

This versioning system is NOT Semantic Versioning.

The version number is derived directly from the Milestone number.

Version Formula:

Version = 0.<Milestone>.0

Examples:

M0  → Version: 0.0.0

M1  → Version: 0.1.0

M2  → Version: 0.2.0

M3  → Version: 0.3.0

M4  → Version: 0.4.0

M5  → Version: 0.5.0

M6  → Version: 0.6.0

M7  → Version: 0.7.0

M8  → Version: 0.8.0

M9  → Version: 0.9.0

M10 → Version: 0.10.0

M11 → Version: 0.11.0

M12 → Version: 0.12.0

M13 → Version: 0.13.0

M14 → Version: 0.14.0

M15 → Version: 0.15.0

M16 → Version: 0.16.0

M17 → Version: 0.17.0

M18 → Version: 0.18.0

M19 → Version: 0.19.0

M20 → Version: 0.20.0

M21 → Version: 0.21.0

M22 → Version: 0.22.0

M23 → Version: 0.23.0

M24 → Version: 0.24.0

M25 → Version: 0.25.0

Rules:

* The second number MUST always equal the Milestone number.
* Do NOT use Semantic Versioning.
* Do NOT increment the Major version automatically.
* Do NOT convert M20 into 1.20.0.
* Do NOT convert M20 into 1.0.0.
* Do NOT reset the numbering sequence.
* The version number must always exactly match the completed Milestone.

Incorrect Examples:

M20 → 1.0.0 ✗

M20 → 1.20.0 ✗

M20 → 0.2.0 ✗

Correct Example:

M20 → 0.20.0 ✓

---

## Git Workflow Rules

For every milestone:

1. Create all changes only inside the current agent branch/worktree.
2. Never modify the main branch directly.
3. Commit all completed work into the current agent branch/worktree.
4. Do not ask the user to manually copy files between branches.
5. Do not create additional branches unless explicitly requested.
6. The implementation branch must always start from the current main branch state.

Main branch updates are performed only after developer review and approval.

---

## Pravidla implementace

Pro každý Milník:

1. Implementuj pouze požadovanou funkcionalitu.
2. Neměň rozsah Milníku.
3. Nepřidávej funkcionalitu z budoucích Milníků.
4. Zachovej strukturu projektu.
5. Zachovej konzistenci design systému.
6. Zachovej kompatibilitu s WordPress 7.
7. Zachovej kompatibilitu s WooCommerce, pokud je součástí projektu.
8. Aktualizuj Version v style.css podle čísla Milníku.
9. Dodržuj pravidla verzování definovaná v sekci Theme Version.

---

## Dokončení Milníku

Před oznámením dokončení Milníku musí Agent:

1. Ověřit implementaci.
2. Aktualizovat Version v style.css.
3. Zkontrolovat git status.
4. Vytvořit commit ve své pracovní větvi/worktree.

Formát commitu:

M<number> - Milestone Name

Příklady:

M1 - Theme Skeleton

M2 - Layout Foundation

M3 - Administration Foundation

M15 - WooCommerce Foundation

M20 - My Account Foundation

Milník není dokončen, dokud není commit úspěšně vytvořen.

Po dokončení Agent vždy vypíše:

* Modified files
* Created files
* Acceptance criteria checklist
* Suggested commit message

Oficiální merge do main provádí výhradně vývojář po kontrole a schválení výsledku.
