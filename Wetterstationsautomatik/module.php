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
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", 0, $this->Translate("Off"), "", -1);
            IPS_SetVariableProfileAssociation("BESCHATTUNG.Switch", 1, $this->Translate("On"), "", -1);
        }
        
        // Variablen für die Beschattung
        $this->RegisterVariableBoolean("Status", "Beschattungsautomatik aktiv?", "BESCHATTUNG.Switch", 1);
        $this->EnableAction("Status");
        $this->RegisterVariableInteger("LuxSollOben", "Helligkeit: Oberen Schwllwert", "~Illumiunation", 2);
        $this->EnableAction("LuxSollOben");
        $this->RegisterVariableInteger("LuxSollUnten", "Helligkeit: Unteren Schwellwert", "~Illumination", 3);
        $this->EnableAction("LuxSollUnten");
        $this->RegisterVariableBoolean("Beschattungsstatus", "Beschattung aktiv?", "BESCHATTUNG.Switch", 4);
        $this->RegisterVariableInteger("AzimutSollVon", "Azimut: Von", "~WindDirection", 5);
        $this->EnableAction("AzimutSollVon");
        $this->RegisterVariableInteger("AzimutSollBis", "Azimut: Bis", "~WindDirection", 6);
        $this->EnableAction("AzimutSollBis");
        
		// Variablen für Wind
        $this->RegisterVariableBoolean("Windstatus", "Windalarm", "~BESCHATTUNG.Switch", 7);
        $this->RegisterVariableInteger("WindSollOben", "Wind: Oberen Schwellwert", "~WindSpeed.kmh", 8);
        $this->EnableAction("WindSOllOben");
        $this->RegisterVariableInteger("WindSollUnten", "Wind: Unteren Schwellwert", "~WindSpeed.kmh", 9);
        $this->EnableAction("WindSollUnten");
        $this->RegisterVariableBoolean("BeschattungWiederholen", "Nach Windalarm Beschattung erneut prüfen?", "BRELAGL.Switch", 10);
        $this->EnableAction("BeschattungWiederholen");
        
        // Variable für Regen
        $this->RegisterVariableBoolean("Regenstatus", "Regen", "~BESCHATTUNG.Switch", 11);
        $this->RegisterVariableBoolean("BeschattungWiederholen2", "Nach Regen Beschattung erneut prüfen?", "BRELAGL.Switch", 12);
        $this->EnableAction("BeschattungWiederholen2");
        
        // Eigenschaften speichern
        $this->RegisterPropertyInteger("Helligkeit", 0);
        $this->RegisterPropertyInteger("Azimut", 0);
        $this->RegisterPropertyInteger("Regensensor", 0);
        $this->RegisterPropertyInteger("Windsensor", 0);
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
        $this->RegisterMessage($this->ReadPropertyInteger("Azimut"), VM_UPDATE);
        $this->RegisterMessage($this->ReadPropertyInteger("Windsensor"), VM_UPDATE);
        $this->RegisterMessage($this->ReadPropertyInteger("Regensensor"), VM_UPDATE);
    }
    
    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
        
        IPS_LogMessage("MessageSink", "Message from SenderID ".$SenderID." with Message ".$Message."\r\n Data: ".print_r($Data, true));
       
        switch ($SenderID) {
            case $this->ReadPropertyInteger("Azimut"):
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
           $WindSollOben = GetValue($this->GetIDForIdent("WindSOllOben"));
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
