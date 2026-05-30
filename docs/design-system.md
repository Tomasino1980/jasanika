# docs/design-system.md

# Design System

## Důležité pravidlo

Tyto barvy jsou závazné.

Copilot Agent nesmí:

* měnit odstíny
* přidávat nové hlavní barvy
* nahrazovat barvy jinými
* vytvářet vlastní barevná schémata

Pokud je potřeba nová barva, musí být nejprve schválena.

---

# Barevná identita

Styl:

Elegant Handmade Boutique

Inspirace:

* ruční výroba
* háčkování
* pedig
* přírodní materiály
* útulný domov
* jemná elegance

---

# Hlavní pozadí

Tmavé pozadí webu.

```css
--bg-main: #1b1a1f;
```

Použití:

* body
* hlavní sekce
* footer

---

# Sekundární pozadí

```css
--bg-secondary: #24212b;
```

Použití:

* oddělené sekce
* dropdown menu
* modální okna

---

# Glass panel

```css
--panel-bg: rgba(255,255,255,0.05);
```

Použití:

* navigace
* karty
* panely

---

# Primární značka

Hlavní fialová.

```css
--primary: #b78acb;
```

Použití:

* tlačítka
* odkazy
* aktivní položky menu
* zvýraznění

---

# Hover primární

```css
--primary-hover: #c79cda;
```

Použití:

* hover efekty
* focus stavy

---

# Akcentní barva

Jemná zlatá.

```css
--accent: #f1c95d;
```

Použití:

* ikonky
* badge
* zvýraznění novinek

Nepoužívat pro velké plochy.

---

# Přírodní akcent

Barva inspirovaná pedigem.

```css
--natural: #b08a67;
```

Použití:

* dekorativní prvky
* přírodní sekce
* handmade prvky

---

# Hlavní text

```css
--text-main: #f5f2f7;
```

Použití:

* nadpisy
* běžný text

---

# Vedlejší text

```css
--text-muted: #b9b1c4;
```

Použití:

* popisy
* metadata
* pomocné informace

---

# Ohraničení

```css
--border-soft: rgba(255,255,255,0.08);
```

Použití:

* karty
* panely
* formuláře

---

# Zakázané barvy

Nepoužívat:

* čistá černá (#000000)
* čistá bílá (#FFFFFF)
* neonové barvy
* křiklavě červená
* gaming RGB efekty
* výrazná modrá
* agresivní zelená

---

# Pravidlo 60 / 30 / 10

60 %

* pozadí
* neutrální plochy

30 %

* sekundární prvky

10 %

* primární fialová a akcenty

---

# Design cíl

Uživatel musí mít pocit:

* útulnosti
* ruční výroby
* elegance
* klidu
* důvěry

Nikdy nesmí vzniknout dojem:

* herního webu
* technického webu
* korporátního webu
* futuristického cyberpunku

Při nejistotě vždy preferuj jednoduchost, čitelnost a konzistenci před novými efekty a novými barvami.