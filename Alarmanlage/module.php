<?
		
    // Klassendefinition
class Alarmanlage extends IPSModule {
        
        // Der Konstruktor des Moduls
        // Überschreibt den Standard Kontruktor von IPS
	public function __construct($InstanceID) {
            // Diese Zeile nicht löschen
            parent::__construct($InstanceID);


            // Selbsterstellter Code
	}
        
        // Überschreibt die interne IPS_Create($id) Funktion
	public function Create() {

            // Diese Zeile nicht löschen.
            parent::Create();

            //Profil für Modusauswahl
            if (!IPS_VariableProfileExists("BRELAG.AlarmModus")) {
        			IPS_CreateVariableProfile("BRELAG.AlarmModus", 1);
        			IPS_SetVariableProfileValues("BRELAG.AlarmModus", 0, 5, 0);
        			IPS_SetVariableProfileIcon("BRELAG.AlarmModus", "IPS");
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 0, $this->Translate("ModeOne"), "", -1);
					IPS_SetVariableProfileAssociation("BRELAG.AlarmModus", 1, $this->Translate("ModeBell"), "", -1);
        		}

            //Progil für Quittierung
            if (!IPS_VariableProfileExists("BRELAG.Quittierung")) {
        			IPS_CreateVariableProfile("BRELAG.Quittierung", 1);
					IPS_SetVariableProfileValues("BRELAG.Quittierung", 0, 4, 0);
        			IPS_SetVariableProfileIcon("BRELAG.Quittierung", "IPS");
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 0, "Alarmmeldung", "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 1, "Sabotage", "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 2, "Batterie", "", -1);
					IPS_SetVariableProfileAssociation("BRELAG.Quittierung", 3, "Lebensdauer", "", -1);
							
        		}

            // Profil für Statusanzeige
            if(!IPS_VariableProfileExists("BRELAG.AlarmStatus")) {
        			IPS_CreateVariableProfile("BRELAG.AlarmStatus", 0);
        			IPS_SetVariableProfileIcon("BRELAG.AlarmStatus", "Power");
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmStatus", 0, $this->Translate("Off"), "", -1);
        			IPS_SetVariableProfileAssociation("BRELAG.AlarmStatus", 1, $this->Translate("On"), "", -1);
            }
            
            // Eigenschaften für Formular
            $this->RegisterPropertyString("Supplement", "[]"); // Liste für boolean Variablen (z.B. Magnetkontakt -> Status). Können auch andere Variablen sein, solange es sich um Boolsche handelt.
            $this->RegisterPropertyInteger("WebFrontName", 0); // Integer Wert für WebFront Auswahl. Wird für die Push-Nachrichten benötigt
            $this->RegisterPropertyString("PushTitel1", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText1", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound1", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon
			$this->RegisterPropertyString("Supplement2", "[]");
            $this->RegisterPropertyString("PushTitel2", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText2", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound2", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon
			$this->RegisterPropertyString("Supplement3", "[]");
			$this->RegisterPropertyString("PushTitel3", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText3", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound3", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon
			$this->RegisterPropertyString("Supplement4", "[]");
            $this->RegisterPropertyString("PushTitel4", ""); // Titel welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("PushText4", ""); // Test welches in der Pusch-Nachricht angezeigt werden soll
            $this->RegisterPropertyString("AlertSound4", ""); // Wählbare Alarm Sounds für Mobilgeräte (siehe Liste von Symcon

			$this->RegisterPropertyBoolean("PushNachrichten3", false);
	    	$this->RegisterPropertyBoolean("PushNachrichten4", false);
            
            // Boolean für Statusanzeige der Alarmanlage, ist inaktiv!
            $this->RegisterVariableBoolean("State", "Status", "BRELAG.AlarmStatus", "0");
            
            // Zeigt der Letzte Alarm im Array (Zeigt nur der letzte Wert vom Array)
            $this->RegisterVariableString("LastAlert", "Letzte Meldung", "", "0");

            // Stringvariable für Passwort Eingabe um Anlage scharf bzw. unschaf zu schalten, ist aktiv!
            $this->RegisterVariableString("Password", "Passwort Eingabe", "", "1");
            $this->EnableAction("Password");           

            // Integervariable für Auswahl der Quittierungen, ist aktiv!
            $this->RegisterVariableInteger("Quittierung", "Quittierung", "BRELAG.Quittierung", "3");
            $this->EnableAction("Quittierung");

            // Stringvariable für ändern des Passworts, Variable "Neues Passwort" verborgen aber beide aktiv!
            $this->RegisterVariableString("OldPassword", "Passwort ändern (aktuelles Password eingeben)", "", "4");
            $this->EnableAction("OldPassword");
            $this->RegisterVariableString("NewPassword", "Neues Passwort", "", "5");
            $this->EnableAction("NewPassword");
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), true);

	    	$this->RegisterVariableInteger("Alarm1", "Alarm 1", "", "10");
	    	$this->RegisterVariableInteger("Alarm2", "Alarm 2", "", "11");
			$this->RegisterVariableInteger("Alarm3", "Alarm 3", "", "12");
	    	$this->RegisterVariableInteger("Alarm4", "Alarm 4", "", "13");


            

            // Test Variablen
                      
        }

        // Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function RequestAction($Ident, $Value) {

              switch($Ident) {
                    case "Password":
                    //Neuen Wert in die Statusvariable schreiben
                      	SetValue($this->GetIDForIdent($Ident), $Value);
                      	$this->Activate();
                    break;
                    
                    case "Quittierung":
                      //Neuen Wert in die Statusvariable schreiben
						SetValue($this->GetIDForIdent($Ident), $Value);
						$this->Quittierung();
                    break;
                    
                    case "OldPassword":
                        //Neuen Wert in die Statusvariable schreiben
                        SetValue($this->GetIDForIdent($Ident), $Value);
                        $this->NewPassword();
                    break;
                    
                    case "NewPassword":
                        //Neuen Wert in die Statusvariable schreiben
                        SetValue($this->GetIDForIdent($Ident), $Value);
                    break;
                    }

      }
	
	public function Quittierung() {

			$AlarmState = GetValue($this->GetIDForIdent("State"));
			$AlarmQuittierung = GetValue($this->GetIDForIdent("Quittierung"));

			switch ($AlarmState)
            {
            	case false:
	    	        switch ($AlarmQuittierung)
    	    	    {
			            case 0:
							SetValue($this->GetIDForIdent("LastAlert"), "");
							SetValue($this->GetIDForIdent("Alarm1"), 0);
						break;
                                    
						case 1: // Quittierung Ereignis (Sabotage) 
							SetValue($this->GetIDForIdent("LastAlert"), "");
							SetValue($this->GetIDForIdent("Alarm2"), 0);
						
							break; 
						case 2: // Quittierung Batterie
							SetValue($this->GetIDForIdent("LastAlert"), "");
							SetValue($this->GetIDForIdent("Alarm3"), 0);
						break;
                                    
						case 3: // Quittierung Lebenszeichen		
							SetValue($this->GetIDForIdent("LastAlert"), "");
							SetValue($this->GetIDForIdent("Alarm4"), 0);
						break;
					}
                                // Platzhalter für Quittierfunktion
				break;
                                
				default:
					echo "Alarm deaktivieren";
				break;
			}
	}
      
	public function Activate() {

            $Password = GetValue($this->GetIDForIdent("Password"));
            $currentPassword = GetValue($this->GetIDForIdent("NewPassword"));
            $State = GetValue($this->GetIDForIdent("State"));


            if($Password == $currentPassword && $State == false)
            {
                SetValue($this->GetIDForIdent("State"), true);
                SetValue($this->GetIDForIdent("Password"), "");
            } elseif($Password == $currentPassword && $State == true)
              {
                SetValue($this->GetIDForIdent("State"), false);
                SetValue($this->GetIDForIdent("Password"), "");
              } elseif ($Password != $currentPassword)
              {
                  SetValue($this->GetIDForIdent("Password"), "");
                  echo "Falsches Passwort, versuch es nochmals";
              }
              
        }
        
        
	public function NewPassword() {


          $Password = GetValue($this->GetIDForIdent("OldPassword"));
          $NewPassword = GetValue($this->GetIDForIdent("NewPassword"));
          $State = GetValue($this->GetIDForIdent("State"));

          if($Password == $NewPassword && $State == false)
          {
            SetValue($this->GetIDForIdent("OldPassword"), "");
            SetValue($this->GetIDForIdent("NewPassword"), "");
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), false);
            IPS_Sleep(15000);
            IPS_SetHidden($this->GetIDForIdent("NewPassword"), true);
            
          } else
            {
                SetValue($this->GetIDForIdent("OldPassword"), "");
                echo "ACHTUNG: Falsches Passwort oder Anlage noch aktiv";                
            }
          

        }

       
        public function StateCheck() {
           
          $array = json_decode($this->ReadPropertyString("Supplement"));
          
          $AlarmAktiv = GetValue($this->GetIDForIdent("State"));
          $Titel1 = $this->ReadPropertyString("PushTitel1");
          $Text1 = $this->ReadPropertyString("PushText1");
		  $AlertSound1 = $this->ReadPropertyString("AlertSound1");
		  		  
		  foreach($array as $arrayID)
		  {
				$VariableName = IPS_GetName($arrayID->ID);
				$VariableStatus = GetValue($arrayID->ID);
			 	$InstanzID = IPS_GetParent($arrayID->ID);
                $InstanzName = IPS_GetName($InstanzID);   	
				$VariableInfo = IPS_GetVariable($arrayID->ID); //Liefert Informationen über die Variable
				$LastChange = $VariableInfo[VariableChanged];
				$Timediff = time() - $LastChange;
				
				switch($VariableStatus)
				{
					case true:
							if($Timediff < 20) {
								switch($AlarmAktiv) {
									case true:
										SetValue($this->GetIDforIdent("LastAlert"), $InstanzName . ' ' . $Text1);
                                    	
                                    	WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel1", "$InstanzName $Text1", "$AlertSound1", $InstanzID);
				    					WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel1", "$InstanzName $Text1");
										SetValue($this->GetIDForIdent("Alarm1"), 1);	

									break;
							}

							}
					break; 
				}	
					 
        }  
   
        }

	public function StateCheck2() {
           
          $array = json_decode($this->ReadPropertyString("Supplement2"));
          
          $AlarmAktiv = GetValue($this->GetIDForIdent("State"));
          $Titel2 = $this->ReadPropertyString("PushTitel2");
          $Text2 = $this->ReadPropertyString("PushText2");
		  $AlertSound2 = $this->ReadPropertyString("AlertSound2");
		  		  
		  foreach($array as $arrayID)
		  {
				$VariableName = IPS_GetName($arrayID->ID);
				$VariableStatus = GetValue($arrayID->ID);
			 	$InstanzID = IPS_GetParent($arrayID->ID);
                $InstanzName = IPS_GetName($InstanzID);   	
				$VariableInfo = IPS_GetVariable($arrayID->ID); //Liefert Informationen über die Variable
    	  		$LastChange = $VariableInfo[VariableChanged];
		  		$Timediff = time() - $LastChange;

				
				switch($VariableStatus)
				{
					case true:
							if($Timediff < 20) {
									SetValue($this->GetIDforIdent("LastAlert"), $InstanzName . ' ' . $Text2);
									SetValue($this->GetIDForIdent("Alarm2"), 1);
									
                                    WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel2", "$InstanzName $Text2", "$AlertSound2", $InstanzID);
				    				WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel2", "$InstanzName $Text2");
										
							}
					break; 
				}	
					 
        }  
   
        }

        public function StateCheck3() {
           
          $array = json_decode($this->ReadPropertyString("Supplement3"));
          
          $AlarmAktiv = GetValue($this->GetIDForIdent("State"));
          $Titel3 = $this->ReadPropertyString("PushTitel3");
          $Text3 = $this->ReadPropertyString("PushText3");
		  $AlertSound3 = $this->ReadPropertyString("AlertSound3");
		  	  
		  foreach($array as $arrayID)
		  {
				$VariableName = IPS_GetName($arrayID->ID);
				$VariableStatus = GetValue($arrayID->ID);
			 	$InstanzID = IPS_GetParent($arrayID->ID);
                $InstanzName = IPS_GetName($InstanzID);   	
				$Push3 = GetValue($this->GetIDForIdent("PushNachrichten3"));
				$VariableInfo = IPS_GetVariable($arrayID->ID); //Liefert Informationen über die Variable
				$LastChange = $VariableInfo[VariableChanged];
		  		$Timediff = time() - $LastChange;

				switch($VariableStatus)
				{
					case true:
							if($Timediff < 20) {
									SetValue($this->GetIDforIdent("LastAlert"), $InstanzName . ' ' . $Text3);
                                    SetValue($this->GetIDForIdent("Alarm3"), 1);	
									
									switch($Push3) {
										case true:
											WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel3", "$InstanzName $Text3", "$AlertSound3", $InstanzID);
											WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel3", "$InstanzName $Text3");
										break;
									}
                            }
					break; 
				}	
					 
        }  
		}
   
	public function StateCheck4() {
           
          $array = json_decode($this->ReadPropertyString("Supplement4"));
          
          $AlarmAktiv = GetValue($this->GetIDForIdent("State"));
          $Titel4 = $this->ReadPropertyString("PushTitel4");
          $Text4 = $this->ReadPropertyString("PushText4");
		  $AlertSound4 = $this->ReadPropertyString("AlertSound4");
		  
		  foreach($array as $arrayID)
		  {
				$VariableName = IPS_GetName($arrayID->ID);
				$VariableStatus = GetValue($arrayID->ID);
			 	$InstanzID = IPS_GetParent($arrayID->ID);
                $InstanzName = IPS_GetName($InstanzID);   	
				$Push4 = GetValue($this->GetIDForIdent("PushNachrichten4"));
				$VariableInfo = IPS_GetVariable($arrayID->ID); //Liefert Informationen über die Variable
			    $LastChange = $VariableInfo[VariableChanged];
		  		$Timediff = time() - $LastChange;

				switch($VariableStatus)
				{
					case true:
							if($Timediff < 20) {
									SetValue($this->GetIDforIdent("LastAlert"), $InstanzName . ' ' . $Text4);
                                    SetValue($this->GetIDForIdent("Alarm4"), 1);	

									switch($Push4) {
										case true:
                                    		WFC_PushNotification($this->ReadPropertyInteger("WebFrontName"), "$Titel4", "$InstanzName $Text4", "$AlertSound4", $InstanzID);
											WFC_SendPopup($this->ReadPropertyInteger("WebFrontName"), "$Titel4", "$InstanzName $Text4");
										break;
									}
							}
					break; 
				}	
			}

	}

    public function ApplyChanges() {
            
            // Diese Zeile nicht löschen
            parent::ApplyChanges();
            
            $StateUpdate = json_decode($this->ReadPropertyString("Supplement"));
			$StateUpdate2 = json_decode($this->ReadPropertyString("Supplement2"));
			$StateUpdate3 = json_decode($this->ReadPropertyString("Supplement3"));
			$StateUpdate4 = json_decode($this->ReadPropertyString("Supplement4"));

            foreach ($StateUpdate as $IDUpdate) {
                $this->RegisterMessage($IDUpdate->ID, VM_UPDATE);
	    	}
            foreach ($StateUpdate2 as $IDUpdate2) {
                $this->RegisterMessage($IDUpdate2->ID, VM_UPDATE);
	    	}
			foreach ($StateUpdate3 as $IDUpdate3) {
                $this->RegisterMessage($IDUpdate3->ID, VM_UPDATE);
	    	}
			foreach ($StateUpdate4 as $IDUpdate4) {
                $this->RegisterMessage($IDUpdate4->ID, VM_UPDATE);
	    	}
	}
        
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
            
            $this->SendDebug("MessageSink", "SenderID: ". $SenderID .", Message: ". $Message , 0);
            $ID = json_decode($this->ReadPropertyString("Supplement"));
			$ID2 = json_decode($this->ReadPropertyString("Supplement2"));
			$ID3 = json_decode($this->ReadPropertyString("Supplement3"));
			$ID4 = json_decode($this->ReadPropertyString("Supplement4"));

			foreach ($ID as $state) {
					if($state->ID == $SenderID)
					{
							$this->StateCheck();
							return;
					}
	    	}
			foreach ($ID2 as $state2) {
					if($state2->ID == $SenderID)
					{
							$this->StateCheck2();
							return;
					}
	    	}
			foreach ($ID3 as $state3) {
					if($state3->ID == $SenderID)
					{
							$this->StateCheck3();
							return;
					}
	    	}
			foreach ($ID4 as $state4) {
					if($state4->ID == $SenderID)
					{
							$this->StateCheck4();
							return;
					}
	    	}
            
	}
        

}
?>
