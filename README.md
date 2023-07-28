### Version 1.0

#### Folgende Variablen werden hinzugefügt 

* Je eine Integervariable für den oberen und unteren Schwellwert für die Helligkeit.
* Je eine Integervariable für den Sonnenazimut von und bis.
* Je eine Integervariable für den oberen und unteren Schwellwert für die Windgeschwindigkeit.
* Eine Boolean für das aktivieren bzw. deaktivieren der Sonnenautomatik.
* Je eine Boolean um die Wiederholung der Beschattungsprüfung nach dem auflösen von Wind oder Regen.
* Je eine Boolean um den Alarmstatus von Wind oder Regen anzuzeigen.
* Eine Boolean die anzeigt ob die Beschattung aktiviert ist oder nicht.
   
#### Funktionen 

Die Variablen Helligkeit, Sonnenazimut, Regensensor und Windsensor werden im Konfigurationsfeld ausgewählt und durch RegisterMessage gespeichert.
* Die Variable "Beschattung aktiv" wird auf true gestellt, wenn: Der Helligkeitswert (ausgelöst durch Sonnenazimut oder de Helligkeitssensor) grösser oder gleich wie der obere Schwellwert ist und der Azimut zwischen dem Wert der Variable "Azimut: Von" und "Azimut: Bis" liegt. Dabei darf es nicht Regnen und der Wind darf den oberen Schwellwert nicht überschritten haben.
* Die Variable "Beschattung aktiv" wird auf false gestellt, wenn: Der Helligkeitswert (ausgelöst durch Sonnenazimut oder de Helligkeitssensor) kleiner oder gleich wie der untere Schwellwert ist oder der Azimutwert höher als der Wert von "Azimut: Bis" ist. Ausserdem wird sie auch auf false gesetzt wenn es beginnt zu Regnen oder der Wind den oberen Schwellwert überschritten hat.
* Die Variable "Regen?" wird auf true gesetzt, wenn: Der Regensensor aktiv ist.
* Die Variable "Windalarm?" wird auf true gestellt, wenn: Der Windsensorwert grösser oder gleich wie der obere Schwellwert ist.
* Die Variable "Windalarm?" wird auf false gestellt, wenn: Der Windsenorwert kleiner wie der untere Schwellwert ist.
* Um eine Beschattung zu realisieren die nicht vom Wind oder Regen abhängig ist (z.B. Rolladen innen im Haus), so kann man in der Modulkonfiguration die entsprechenden Sensoren einfach nicht angeben.

#### Konfigurationsformular

Auswahlmöglichkeit von folgenden Variablen: Lichtsensor, Sonenazimut, Regensensor und Windsensor.

#### Zusätzliche Funktionen dieses Modules

* BRELAG_BeschattungAktivieren()
* BRELAG_BeschattungWiederholen()
* BRELAG_Regenalarm()
* BRELAG_Windalarm()

#### Zusätzliche Variablenprofile dieses Modules

* BESCHATTUNG.Switch
* BESCHATTUNG.SwitchSonne
* BESCHATTUNG.SwitchAlarm
* SchwellwertWind
* SchwellwertSonne
* SchwellwertAzimut
