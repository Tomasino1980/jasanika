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

## Odpovědnosti

### Copilot Agent

* vytváří kód
* upravuje soubory
* navrhuje řešení

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

## Důležité pravidlo

Agent nikdy nevytváří oficiální historii projektu.

Historii projektu vytváří pouze vývojář.
