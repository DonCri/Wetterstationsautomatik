<?php

// Klassendefinition
class Wetterstationsautomatik extends IPSModule
{
    /**
    * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
    * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
    *
    * ABC_MeineErsteEigeneFunktion($id);
    *
    */
          
        

    // Überschreibt die interne IPS_Create($id) Funktion
    public function Create()
    {
        parent::Create();
            
        // Profile
        if (!IPS_VariableProfileExists("BESCHATTUNG.Switch")) {
            IPS_CreateVariableProfile("BESCHATTUNG.Switch", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.Switch", "Power");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", false, $this->Translate("Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", true, $this->Translate("On"), "", 0x3ADF00);
        }
                
        if (!IPS_VariableProfileExists("BESCHATTUNG.SwitchSonne")) {
            IPS_CreateVariableProfile("BESCHATTUNG.SwitchSonne", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.SwitchSonne", "Sun");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchSonne", false, $this->Translate("Beschattung-Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchSonne", true, $this->Translate("Beschattung-On"), "", 0x3ADF00);
        }
            
        if (!IPS_VariableProfileExists("BESCHATTUNG.SwitchAlarm")) {
            IPS_CreateVariableProfile("BESCHATTUNG.SwitchAlarm", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.SwitchAlarm", "Alert");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchAlarm", false, $this->Translate("Alarm-Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchAlarm", true, $this->Translate("Alarm-On"), "", 0xDF0101);
        }
                
        if (!IPS_VariableProfileExists("SchwellwertWind")) {
            IPS_CreateVariableProfile("SchwellwertWind", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertWind", "WindSpeed");
            IPS_SetVariableProfileText("SchwellwertWind", "", " km/h");
        }
                
        if (!IPS_VariableProfileExists("SchwellwertSonne")) {
            IPS_CreateVariableProfile("SchwellwertSonne", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertSonne", "Sun");
            IPS_SetVariableProfileText("SchwellwertSonne", "", " lx");
        }

        if (!IPS_VariableProfileExists("SchwellwertAzimut")) {
            IPS_CreateVariableProfile("SchwellwertAzimut", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertAzimut", "WindDirection");
            IPS_SetVariableProfileText("SchwellwertAzimut", "", "°");
        }
            
        // Variablen für die Beschattung
        $this->RegisterVariableBoolean("Status", "Beschattungsautomatik Aktiv", "BESCHATTUNG.Switch", 1);
        $this->EnableAction("Status");
        $this->RegisterVariableBoolean("BeschattungWiederholen", "Nach Windalarm Beschattung erneut prüfen", "BESCHATTUNG.Switch", 10);
        $this->EnableAction("BeschattungWiederholen");
        $this->RegisterVariableBoolean("BeschattungWiederholen2", "Nach Regen Beschattung erneut prüfen", "BESCHATTUNG.Switch", 12);
        $this->EnableAction("BeschattungWiederholen2");

        $this->RegisterVariableString("LuxSollOben", "Helligkeit: Oberen Schwellwert", "SchwellwertSonne", 2);
        $this->EnableAction("LuxSollOben");
        $this->RegisterVariableString("LuxSollUnten", "Helligkeit: Unteren Schwellwert", "SchwellwertSonne", 3);
        $this->EnableAction("LuxSollUnten");
        $this->RegisterVariableString("AzimutSollVon", "Azimut: Von", "SchwellwertAzimut", 5);
        $this->EnableAction("AzimutSollVon");
        $this->RegisterVariableString("AzimutSollBis", "Azimut: Bis", "SchwellwertAzimut", 6);
        $this->EnableAction("AzimutSollBis");
        $this->RegisterVariableBoolean("Beschattungsstatus", "Licht: Alarm", "BESCHATTUNG.SwitchSonne", 4);
            
        // Variablen für Wind
        $this->RegisterVariableString("WindSollOben", "Wind: Oberen Schwellwert", "SchwellwertWind", 8);
        $this->EnableAction("WindSollOben");
        $this->RegisterVariableString("WindSollUnten", "Wind: Unteren Schwellwert", "SchwellwertWind", 9);
        $this->EnableAction("WindSollUnten");
        $this->RegisterVariableBoolean("Windstatus", "Wind: Alarm", "BESCHATTUNG.SwitchAlarm", 7);
            
        // Variable für Regen
        $this->RegisterVariableBoolean("Regenstatus", "Regen: Alarm", "BESCHATTUNG.SwitchAlarm", 11);
            
        // Eigenschaften speichern
        $this->RegisterPropertyInteger("Helligkeit", 0);
        $this->RegisterPropertyInteger("Azimut", 0);
        $this->RegisterPropertyInteger("Regensensor", 0);
        $this->RegisterPropertyInteger("Windsensor", 0);
        $this->RegisterPropertyBoolean("LichtsensorAktiv", false);
    }

    public function RequestAction($Ident, $Value)
    {
        $IdentID = $this->GetIDForIdent($Ident);
        if ($IdentID) {
            SetValue(IdentID, $Value);
        }
    }
    
    public function ApplyChanges()
    {
        parent::ApplyChanges();
        
        if ($this->ReadPropertyBoolean("LichtsensorAktiv")) {
            $this->RegisterMessage($this->ReadPropertyInteger("Helligkeit"), 10603 /* VM_UPDATE */);
            $this->UnregisterMessage($this->ReadPropertyInteger("Azimut"), 10603 /* VM_UPDATE */);
        } else {
            $this->RegisterMessage($this->ReadPropertyInteger("Azimut"), 10603 /* VM_UPDATE */);
            $this->UnregisterMessage($this->ReadPropertyInteger("Helligkeit"), 10603 /* VM_UPDATE */);
        }
        $this->RegisterMessage($this->ReadPropertyInteger("Windsensor"), 10603 /* VM_UPDATE */);
        $this->RegisterMessage($this->ReadPropertyInteger("Regensensor"), 10603 /* VM_UPDATE */);
    }
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data)
    {
        IPS_LogMessage($_IPS['SELF'], "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
        $this->SendDebug($_IPS['SELF'], "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true),0);

        //$LichtsensorAktiv = $this->ReadPropertyBoolean("LichtsensorAktiv");
        $Helligkeit = $this->ReadPropertyInteger("Helligkeit");
        $Azimut = $this->ReadPropertyInteger("Azimut");
        $Windsensor = $this->ReadPropertyInteger("Windsensor");
        $Regensensor = $this->ReadPropertyInteger("Regensensor");

        switch ($SenderID) {
            case $Helligkeit:
            case $Azimut:
                $this->BeschattungAktivieren(); // Wird in beiden fällen ausgelöst, Aktualisierung von $Helligkeit und $Azimut
            break;
            case $Windsensor:
                $this->Windalarm();
            break;
            case $Regensensor:
                $this->Regenalarm();
            break;
        }
    }

    public function BeschattungAktivieren()
    {
        //$LichtsensorAktiv = $this->ReadPropertyBoolean("LichtsensorAktiv");
        $Status = GetValue($this->GetIDForIdent("Status"));     // gibt an ob Beschattungsautomatik aktiv oder inaktiv ist
        $HelligkeitWert = GetValue($this->ReadPropertyInteger("Helligkeit"));
        $LuxSollOben = GetValue($this->GetIDForIdent("LuxSollOben"));
        $LuxSollUnten = GetValue($this->GetIDForIdent("LuxSollUnten"));
        $AzimutWert = GetValue($this->ReadPropertyInteger("Azimut"));
        $AzimutSollVon = GetValue($this->GetIDForIdent("AzimutSollVon"));
        $AzimutSollBis = GetValue($this->GetIDForIdent("AzimutSollBis"));
        $RegenWert = GetValue($this->GetIDForIdent("Regenstatus"));
        $Beschattungsstatus = GetValue($this->GetIDForIdent("Beschattungsstatus"));

        if ($Status) { // Beschattungsautomatik aktiv
            if ($Beschattungsstatus) { // Licht Alarm wurde bereits ausgelöst
                if ($Azimut > $AzimutSollBis || $Helligkeit <= $LuxSollUnten) {
                    SetValue($this->GetIDForIdent("Beschattungsstatus"), false);
                }
            } else {
                if ($Azimut >= $AzimutSollVon && $Azimut <= $AzimutSollBis && $Helligkeit >= $LuxSollOben && $Regen == false) {
                    SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
                }
            }
        }
    }
        
    public function BeschattungWiederholen()
    {
        $Status = GetValue($this->GetIDForIdent("Status"));
        $Helligkeit = GetValue($this->ReadPropertyInteger("Helligkeit"));
        $LuxSollOben = GetValue($this->GetIDForIdent("LuxSollOben"));
        $Azimut = GetValue($this->ReadPropertyInteger("Azimut"));
        $AzimutSollVon = GetValue($this->GetIDForIdent("AzimutSollVon"));
        $AzimutSollBis = GetValue($this->GetIDForIdent("AzimutSollBis"));
        $Regen = GetValue($this->ReadPropertyInteger("Regenstatus"));
           
        if($Status) {
            if ($Azimut >= $AzimutSollVon && $Azimut <= $AzimutSollBis && $Helligkeit >= $LuxSollOben && $Regen == false) {
                SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
            }
        }
    }
       
    public function Windalarm()
    {
        $Windsensor = GetValue($this->ReadPropertyInteger("Windsensor"));
        $WindSollOben = GetValue($this->GetIDForIdent("WindSollOben"));
        $WindSollUnten = GetValue($this->GetIDForIdent("WindSollUnten"));
        $Beschattung = GetValue($this->GetIDForIdent("BeschattungWiederholen"));
           
        if ($Windsensor >= $WindSollOben) {
            SetValue($this->GetIDForIdent("Windstatus"), true);
        } elseif ($Windsensor <= $WindSollUnten) {
            SetValue($this->GetIDForIdent("Windstatus"), false);
            if ($Beschattung == true) {
                $this->BeschattungWiederholen();
            }
        }
    }
       
    public function Regenalarm()
    {
        $Regensensor = GetValue($this->ReadPropertyInteger("Regensensor"));
        $Beschattung = GetValue($this->GetIDForIdent("BeschattungWiederholen"));
           
        if ($Regensensor) {
            SetValue($this->GetIDForIdent("Regenstatus"), true);
        } else {
            SetValue($this->GetIDForIdent("Regenstatus"), false);
            if ($Beschattung == true) {
                $this->BeschattungWiederholen();
            }
        }
    }
}
