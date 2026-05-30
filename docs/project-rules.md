# Jasanika Handmade

WordPress 7 e-shop a prezentace ručně vyráběných dekorací, výrobků z pedigu, háčkovaných výrobků a domácích doplňků.

## Hlavní cíl

Vytvořit jednoduchý, rychlý, přehledný a dlouhodobě udržovatelný web.

Projekt musí být snadno pochopitelný i po několika letech bez nutnosti studovat externí frameworky.

---

## Použité technologie

### Povolené

* WordPress 7
* PHP
* HTML5
* CSS3
* Vanilla JavaScript

### Nepovolené

* React
* Vue
* Angular
* Bootstrap
* Tailwind
* jQuery
* Elementor
* WPBakery
* Divi Builder

---

## Architektonické zásady

* Mobile First
* Komponentový přístup
* Jedna komponenta = jeden účel
* Žádné zbytečné abstrakce
* Žádné externí knihovny bez schválení
* Kód musí být čitelný a komentovaný

---

## Design

Styl:

* Elegant Handmade Boutique
* Tmavé pozadí
* Jemná fialová
* Glassmorphism
* Pastelové akcenty

Veškeré barvy jsou definovány v dokumentu:

docs/design-system.md

---

## Pravidla pro Copilot Agent

Nevytvářej:

* nové frameworky
* utility knihovny
* build systémy
* zbytečné závislosti

Používej pouze strukturu definovanou v projektu.

Při vytváření nových komponent vždy zachovej stávající design systém.

---

## Git Workflow

Copilot Agent není správce projektu.

Copilot Agent není správce Git historie.

Copilot Agent pouze vytváří nebo upravuje soubory.

Každý Milník:

1. Agent vytvoří změny.
2. Změny se zkontrolují.
3. Změny se otestují.
4. Pokud jsou správně:

   * převezmou se do MAIN
   * vytvoří se commit Milníku
   * odešlou se na GitHub
5. Pokud jsou chybné:

   * Session se ukončí
   * vytvoří se nová Session
   * Milník se zadá znovu

Historii projektu vytváří pouze vývojář.
