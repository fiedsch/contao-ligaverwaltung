# Ligaverwaltung für Contao Open Source CMS

* ohne Version, da erst im entstehen 
* so lange hauptsächlich als Backup
* Fragen und Anregungen sind aber dennoch gerne gesehen
* Lizenz: MIT
* [Als Bundle Für Contao 4](https://github.com/fiedsch/contao-ligaverwaltung-bundle)

## Konfiguration

In den Contao Systemeinstellungen kann im Bereich "Ligaverwaltung" festgelegt werden, wie einzelne
Spieler zeitgleich in verscheidenen Mannschaften spielen dürfen. Die dort getroffene Auswahl bestimmt
die Spieler, die beim Hinzufügen zu einer Mannschaft im Auswahlmenü angezeigt werden:
* "in einer Mannschft (je Saison)": ein Spieler darf in einer Saison (ligaübergreifend) nur in einer 
  Mannschaft spielen
* "in einer Mannschft (je Liga)" — weniger restriktiv: ein Spieler darf in einer Liga nur in einer 
  Mannschaft spielen. In einer anderen Liga darf er aber zeitgleich auch spielen!
  
## Datenstrukturen
  
```
tl_spielort (Marker; wird einer Mannschaft zugeordnet)

tl_aufsteller (Marker; wird einem Spielort zugeordnet)
  
tl_saison (Marker; wird einer Liga zugeordnet)
  
tl_verband
   |
   + tl_liga
        |
        + tl_begegnung (Mannschaft gegen Mannschaft)
            |
            + tl_spiel (Spieler gegen Spieler)
          
tl_mannschaft (hat als Attribut (u.A.) eine Liga, ist aber im Sinne der Contao DCA keine 
Kindtabelle von tl_liga!)
   |
   + tl_spieler (Mapping-Tabelle, die einen Spieler in einer Mannschaft -- und damit Liga
     und damit Saison -- auf ein Contao-Member abbildet).

tl_highlight (dient der Erfassung von Highlights wie High-Finishes oder Shortlegs)

tl_spielort Verwaltung von Spielorten (Attribut einer Mannschart)
```