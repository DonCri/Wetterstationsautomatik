<?

    // Klassendefinition
    class Wetterstationsautomatik extends IPSModule {
        /**
        * Die folgenden Funktionen stehen automatisch zur Verfügung, wenn das Modul über die "Module Control" eingefügt wurden.
        * Die Funktionen werden, mit dem selbst eingerichteten Prefix, in PHP und JSON-RPC wiefolgt zur Verfügung gestellt:
        *
        * ABC_MeineErsteEigeneFunktion($id);
        *
        */
          
        

        // Überschreibt die interne IPS_Create($id) Funktion
        public function Create() {
        // Diese Zeile nicht löschen.
        parent::Create();
		
        // Profile
        if(!IPS_VariableProfileExists("BESCHATTUNG.Switch")) {
            IPS_CreateVariableProfile("BESCHATTUNG.Switch", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.Switch", "Power");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", false, $this->Translate("Off"), "", -1,);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", true, $this->Translate("On"), "", 0x3ADF00);
            }
            
        if(!IPS_VariableProfileExists("BESCHATTUNG.SwitchSonne")) {
            IPS_CreateVariableProfile("BESCHATTUNG.SwitchSonne", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.SwitchSonne", "Sun");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchSonne", false, $this->Translate("Beschattung-Off"), "", -1,);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchSonne", true, $this->Translate("Beschattung-On"), "", 0x3ADF00);
            }
         
        if(!IPS_VariableProfileExists("BESCHATTUNG.SwitchAlarm")) {
            IPS_CreateVariableProfile("BESCHATTUNG.SwitchAlarm", 0);
            IPS_SetVariableProfileIcon("BESCHATTUNG.SwitchAlarm", "Alert");
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchAlarm", false, $this->Translate("Alarm-Off"), "", -1,);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.SwitchAlarm", true, $this->Translate("Alarm-On"), "", 0xDF0101);
            }
            
        if(!IPS_VariableProfileExists("SchwellwertWind")) {
            IPS_CreateVariableProfile("SchwellwertWind", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertWind", "WindSpeed");
            IPS_SetVariableProfileText("SchwellwertWind", "", " km/h");
            }
            
        if(!IPS_VariableProfileExists("SchwellwertSonne")) {
            IPS_CreateVariableProfile("SchwellwertSonne", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertSonne", "Sun");
            IPS_SetVariableProfileText("SchwellwertSonne", "", " lx");
            }
        if(!IPS_VariableProfileExists("SchwellwertAzimut")) {
            IPS_CreateVariableProfile("SchwellwertAzimut", 3); // 0 = Boolean, 1 = Integer, 2 = Float, 3 = String
            IPS_SetVariableProfileIcon("SchwellwertAzimut", "WindDirection");
            IPS_SetVariableProfileText("SchwellwertAzimut", "", "°");
            }
        
        // Variablen für die Beschattung
        $this->RegisterVariableBoolean("Status", "Beschattungsautomatik aktiv?", "BESCHATTUNG.Switch", 1);
        $this->EnableAction("Status");
        $this->RegisterVariableString("LuxSollOben", "Helligkeit: Oberen Schwllwert", "SchwellwertSonne", 2);
        $this->EnableAction("LuxSollOben");
        $this->RegisterVariableString("LuxSollUnten", "Helligkeit: Unteren Schwellwert", "SchwellwertSonne", 3);
        $this->EnableAction("LuxSollUnten");
        $this->RegisterVariableBoolean("Beschattungsstatus", "Beschattung aktiv?", "BESCHATTUNG.SwitchSonne", 4);
        $this->RegisterVariableString("AzimutSollVon", "Azimut: Von", "SchwellwertAzimut", 5);
        $this->EnableAction("AzimutSollVon");
        $this->RegisterVariableString("AzimutSollBis", "Azimut: Bis", "SchwellwertAzimut", 6);
        $this->EnableAction("AzimutSollBis");
        
		// Variablen für Wind
        $this->RegisterVariableBoolean("Windstatus", "Windalarm?", "BESCHATTUNG.SwitchAlarm", 7);
        $this->RegisterVariableString("WindSollOben", "Wind: Oberen Schwellwert", "SchwellwertWind", 8);
        $this->EnableAction("WindSollOben");
        $this->RegisterVariableString("WindSollUnten", "Wind: Unteren Schwellwert", "SchwellwertWind", 9);
        $this->EnableAction("WindSollUnten");
        $this->RegisterVariableBoolean("BeschattungWiederholen", "Nach Windalarm Beschattung erneut prüfen?", "BESCHATTUNG.Switch", 10);
        $this->EnableAction("BeschattungWiederholen");
        
        // Variable für Regen
        $this->RegisterVariableBoolean("Regenstatus", "Regen?", "BESCHATTUNG.SwitchAlarm", 11);
        $this->RegisterVariableBoolean("BeschattungWiederholen2", "Nach Regen Beschattung erneut prüfen?", "BESCHATTUNG.Switch", 12);
        $this->EnableAction("BeschattungWiederholen2");
        
        // Eigenschaften speichern
        $this->RegisterPropertyInteger("Helligkeit", 0);
        $this->RegisterPropertyInteger("Azimut", 0);
        $this->RegisterPropertyInteger("Regensensor", 0);
        $this->RegisterPropertyInteger("Windsensor", 0);
        $this->RegisterPropertyBoolean("LichtsensorAktiv", false);
      }

      public function RequestAction($Ident, $Value) {

            switch($Ident) {
                  case "Status":
                  //Neuen Wert in die Statusvariable schreiben
                    SetValue($this->GetIDForIdent("Status"), $Value);
                  break;
                  case "LuxSollOben":
                    //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent("LuxSollOben"), $Value);
                  break;
                  case "LuxSollUnten":
                    //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent("LuxSollUnten"), $Value);
                  break;
                  case "AzimutSollVon":
                      //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent("AzimutSollVon"), $Value);
                  break;
                  case "AzimutSollBis":
                      //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent("AzimutSollBis"), $Value);
                  break;
                  case "WindSollOben":
                      //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent("WindSollOben"), $Value);
                      break;
                  case "WindSollUnten":
                      //Neuen Wert in die Statusvariable schreiben
                      SetValue($this->GetIDForIdent("WindSollUnten"), $Value);
                      break;
                  case "BeschattungWiederholen":
                      SetValue($this->GetIDForIdent("BeschattungWiederholen"), $Value);
                  break;
                  case "BeschattungWiederholen2":
                      SetValue($this->GetIDForIdent("BeschattungWiederholen2"), $Value);
                      break;
                  }

    }
    
    public function ApplyChanges() {
        
        // Diese Zeile nicht löschen
        parent::ApplyChanges();
        
        // Wenn keine übergeordenete Instanz vorhanden ist, erstelle eine neue eigene VirtualIO Instanz
        
        switch ($this->ReadPropertyBoolean("LichtsensorAktiv")) {
            case true:
                $this->RegisterMessage($this->ReadPropertyBoolean("LichtsensorAktiv"), VM_UPDATE);
                $this->UnregisterMessage($this->ReadPropertyInteger("Azimut"), VM_UPDATE);
            break;
            
            case false:
                $this->RegisterMessage($this->ReadPropertyInteger("Azimut"), VM_UPDATE);
                $this->UnregisterMessage($this->ReadPropertyBoolean("LichtsensorAktiv"), VM_UPDATE);
            break;
        }
        
        $this->RegisterMessage($this->ReadPropertyInteger("Windsensor"), VM_UPDATE);
        $this->RegisterMessage($this->ReadPropertyInteger("Regensensor"), VM_UPDATE);
    }
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        
        IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
       
        switch ($SenderID) {
            case $this->ReadPropertyInteger("Azimut"):
                    $this->BeschattungAktivieren();
                break;
            case $this->ReadPropertyBoolean("LichtsensorAktiv"):
                $this->BeschattungAktivieren();
                break;
            case $this->ReadPropertyInteger("Windsensor"):
                    $this->Windalarm();
                break;
            case $this->ReadPropertyInteger("Regensensor"):
                    $this->Regenalarm();
                break;
        }
    }

      public function BeschattungAktivieren() {
            
            $Status = GetValue($this->GetIDForIdent("Status"));
            $Helligkeit = GetValue($this->ReadPropertyInteger("Helligkeit"));
            $LuxSollOben = GetValue($this->GetIDForIdent("LuxSollOben"));
            $LuxSollUnten = GetValue($this->GetIDForIdent("LuxSollOben"));
            $Azimut = GetValue($this->ReadPropertyInteger("Azimut"));
            $AzimutSollVon = GetValue($this->GetIDForIdent("AzimutSollVon"));
            $AzimutSollBis = GetValue($this->GetIDForIdent("AzimutSollBis"));
            $Regen = GetValue($this->ReadPropertyInteger("Regensensor"));
            $Beschattungsstatus = GetValue($this->GetIDForIdent("Beschattungsstatus"));
            
            switch ($Status) {
                case true:
                    switch ($Beschattungsstatus) {
                        case false:
                            if($Azimut >= $AzimutSollVon && $Azimut < $AzimutSollBis && $Helligkeit >= $LuxSollOben) {
                                SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
                            }
                            break;
                        case true:
                            if ($Azimut > $AzimutSollBis || $Helligkeit <= $LuxSollUnten) {
                                SetValue($this->GetIDForIdent("Beschattungsstatus"), false);
                            }
                            break;
                    }
                    break;
            }
        }
        
       public function BeschattungWiederholen() {
           
           $Status = GetValue($this->GetIDForIdent("Status"));
           $Helligkeit = GetValue($this->ReadPropertyInteger("Helligkeit"));
           $LuxSollOben = GetValue($this->GetIDForIdent("LuxSollOben"));
           $LuxSollUnten = GetValue($this->GetIDForIdent("LuxSollOben"));
           $Azimut = GetValue($this->ReadPropertyInteger("Azimut"));
           $AzimutSollVon = GetValue($this->GetIDForIdent("AzimutSollVon"));
           $AzimutSollBis = GetValue($this->GetIDForIdent("AzimutSollBis"));
           $Regen = GetValue($this->ReadPropertyInteger("Regensensor"));
           
           switch ($Status) {
               case true:
                       if($Azimut >= $AzimutSollVon && $Azimut < $AzimutSollBis && $Helligkeit >= $LuxSollOben) {
                           SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
                       }
                   break;
           }
       }
       
       public function Windalarm() {
           
           $Windsensor = GetValue($this->ReadPropertyInteger("Windsensor"));
           $WindSollOben = GetValue($this->GetIDForIdent("WindSollOben"));
           $WindSollUnten = GetValue($this->GetIDForIdent("WindSollUnten"));
           $Beschattung = GetValue($this->GetIDForIdent("BeschattungWiederholen"));
           
           if($Windsensor >= $WindSollOben) {
               SetValue($this->GetIDForIdent("Windstatus"), true);
           } elseif($Windsensor < $WindSollUnten) {
               SetValue($this->GetIDForIdent("Windstatus"), false);
               if($Beschattung == true) {
                   SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
               }
           }
       }
       
       public function Regenalarm() {
           
           $Regensensor = GetValue($this->ReadPropertyInteger("Regensensor"));
           $Beschattung = GetValue($this->GetIDForIdent("BeschattungWiederholen"));
           
           switch ($Regensensor) {
               case true:
                    SetValue($this->GetIDForIdent("Regenstatus"), true);
                   break;
               case false:
                    SetValue($this->GetIDForIdent("Regenstatus"), false);
                    if($Beschattung == true) {
                        SetValue($this->GetIDForIdent("Beschattungsstatus"), true);
                    }
                   break;
           }
       }

}

?>
