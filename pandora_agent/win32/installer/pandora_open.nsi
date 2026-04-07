;======================================================
; NSIS SCRIPT TO CREATE PANDORAOPEN AGENT
;======================================================

!include "MUI2.nsh"
!include "nsDialogs.nsh"
!include "logiclib.nsh"
!include "x64.nsh"
!include "FileFunc.nsh"

; --- Standard String Functions Library ---
!include "StrFunc.nsh"
${StrRep}
${UnStrRep}

; --- Macros ---
!macro DetectArchitecture
  !ifndef ARCH
    !tempfile DETECT_TEMP
    !system "file '${REPO_PATH}/PandoraAgent.exe' | grep -q 'PE32+' && echo 'x64' > '${DETECT_TEMP}' || echo 'x86' > '${DETECT_TEMP}'"
    !searchparse /file '${DETECT_TEMP}' '' ARCH ''
    !delfile '${DETECT_TEMP}'
    !if "${ARCH}" == ""
      !error "Failed to detect architecture of PandoraAgent.exe."
    !endif
  !endif
!macroend

!macro AddToPath Path
  ReadRegStr $0 HKCU "Environment" "PATH"
  ${StrRep} $1 "$0" "${Path}" ""
  ${If} $1 == $0
    StrCpy $0 "$0;${Path}"
    WriteRegExpandStr HKCU "Environment" "PATH" $0
    SendMessage ${HWND_BROADCAST} ${WM_WININICHANGE} 0 "STR:Environment" /TIMEOUT=5000
  ${EndIf}
!macroend

!macro un.RemoveFromPath Path
  ReadRegStr $0 HKCU "Environment" "PATH"
  ${UnStrRep} $0 "$0" "${Path};" ""
  ${UnStrRep} $0 "$0" "${Path}" ""
  WriteRegExpandStr HKCU "Environment" "PATH" $0
  SendMessage ${HWND_BROADCAST} ${WM_WININICHANGE} 0 "STR:Environment" /TIMEOUT=5000
!macroend

; --- Defines ---
!define PRODUCT_NAME "PandoraOpenAgent"
!define PRODUCT_DIR_REGKEY "Software\Microsoft\Windows\CurrentVersion\App Paths\PandoraAgent.exe"
!define PRODUCT_UNINST_KEY "Software\Microsoft\Windows\CurrentVersion\Uninstall\${PRODUCT_NAME}"
!define PRODUCT_UNINST_ROOT_KEY "HKLM"

!insertmacro DetectArchitecture

!if "${ARCH}" == "x86"
  !define INSTALL_DIR "$PROGRAMFILES32\pandora_agent"
!else
  !define INSTALL_DIR "$PROGRAMFILES64\pandora_agent"
!endif

; --- MUI Settings ---
!define MUI_ABORTWARNING
!define MUI_ICON "${REPO_PATH}/installer/pandora.ico"
!define MUI_WELCOMEFINISHPAGE_BITMAP "${REPO_PATH}/installer/pandora_side.bmp"
!define MUI_WELCOMEPAGE_TITLE "Pandora Open Agent ${PRODUCT_VERSION} Windows"

!insertmacro MUI_PAGE_WELCOME
!insertmacro MUI_PAGE_LICENSE "${REPO_PATH}/installer/LICENSE.txt"
!insertmacro MUI_PAGE_DIRECTORY
Page custom ipaddress ipaddress_leave
!insertmacro MUI_PAGE_INSTFILES
!define MUI_FINISHPAGE_RUN "cmd.exe"
!define MUI_FINISHPAGE_RUN_PARAMETERS "/c @net start PandoraOpenAgent"
!insertmacro MUI_PAGE_FINISH
!insertmacro MUI_UNPAGE_INSTFILES
!insertmacro MUI_LANGUAGE "English"
!insertmacro MUI_LANGUAGE "Spanish"

Name "Pandora Open Agent ${PRODUCT_VERSION}"
OutFile "${FILE_NAME}"
InstallDir "${INSTALL_DIR}"
InstallDirRegKey HKLM "${PRODUCT_DIR_REGKEY}" ""
ShowInstDetails show
BrandingText "PandoraOpen"

Var _nl_
Var dialog
Var hIpText
Var serverIpConf

Function .onInit
  StrCpy $_nl_ "$\r$\n"
  StrCpy $serverIpConf "localhost"
FunctionEnd

; --- Page Functions ---

Function ipaddress
    nsDialogs::Create 1018
    Pop $dialog
    ${If} $dialog == error
        Abort
    ${EndIf}

    ${If} $LANGUAGE == ${LANG_SPANISH}
        !insertmacro MUI_HEADER_TEXT "Configuración del Servidor" "Configuración de conexión"
        ${NSD_CreateLabel} 0 0 100% 20u "Escriba a continuación la dirección IP o el nombre del servidor de Pandora Open."
        Pop $0
        ${NSD_CreateLabel} 0 30u 100u 12u "Servidor de Pandora Open:"
        Pop $0
    ${Else}
        !insertmacro MUI_HEADER_TEXT "Server Configuration" "Connection settings"
        ${NSD_CreateLabel} 0 0 100% 20u "Enter the IP address or FQDN of your Pandora Open server."
        Pop $0
        ${NSD_CreateLabel} 0 30u 100u 12u "Pandora Open Server:"
        Pop $0
    ${EndIf}

    ${NSD_CreateText} 120u 28u 150u 12u "$serverIpConf"
    Pop $hIpText

    nsDialogs::Show
FunctionEnd

Function ipaddress_leave
    ${NSD_GetText} $hIpText $serverIpConf
FunctionEnd

; --- Installation Sections ---

Section "Main Installation" SEC01
  ; Stop service if running
  ExecWait 'net stop PandoraOpenAgent'
  
  SetOutPath "$INSTDIR"
  CreateDirectory "$INSTDIR\temp"
  CreateDirectory "$INSTDIR\scripts"
  CreateDirectory "$INSTDIR\util"
  CreateDirectory "$INSTDIR\util\mibs"
  CreateDirectory "$INSTDIR\collections"
  CreateDirectory "$INSTDIR\ref"
  
  File "${REPO_PATH}/PandoraAgent.exe"
  File "${REPO_PATH}/installer/LICENSE.txt"
  File "${REPO_PATH}/installer/pandora.ico"
  
  ; UPGRADE PROTECTION: Only extract default config if one doesn't exist
  IfFileExists "$INSTDIR\pandora_agent.conf" skip_conf_extract
    File "/oname=pandora_agent.conf" "${REPO_PATH}/bin/pandora_agent.conf"
    Call modify_conf_file
  skip_conf_extract:
  
  SetOutPath "$INSTDIR\util"
  File "${REPO_PATH}/bin/util/*.*"
  ${If} "${ARCH}" == "x86"
    File "${REPO_PATH}/bin/util/getreg.exe"
  ${Else}
    File "${REPO_PATH}/bin/util/x64/getreg.exe"
  ${EndIf}
  
  SetOutPath "$INSTDIR\util\mibs"
  File "${REPO_PATH}/bin/util/mibs/*.*"
  
  SetOutPath "$INSTDIR\scripts"
  File "${REPO_PATH}/installer/scripts/*.*"
  
  ; Create/Overwrite the edit batch file to ensure paths are correct
  FileOpen $4 "$INSTDIR\scripts\edit_config_file.bat" w
  FileWrite $4 '@start "" "$INSTDIR\pandora_agent.conf"$_nl_'
  FileClose $4
  
  ExecWait '"$INSTDIR\PandoraAgent.exe" --install'
  !insertmacro AddToPath "$INSTDIR\util"
  
  ; Scheduled Task
  nsExec::Exec 'schtasks /Create /TN pandora_agent_restart /TR "\"$INSTDIR\scripts\restart_pandora_agent.bat\"" /SC DAILY /ST 00:00:00 /F /RU SYSTEM'
SectionEnd

Section "Start Menu Shortcuts"
  SetShellVarContext all
  CreateDirectory "$SMPROGRAMS\${PRODUCT_NAME}_v${PRODUCT_VERSION}"
  CreateShortCut "$SMPROGRAMS\${PRODUCT_NAME}_v${PRODUCT_VERSION}\Uninstall.lnk" "$INSTDIR\uninst.exe"
  CreateShortCut "$SMPROGRAMS\${PRODUCT_NAME}_v${PRODUCT_VERSION}\Edit Config.lnk" "$INSTDIR\scripts\edit_config_file.bat" "" "$INSTDIR\pandora.ico"
  CreateShortCut "$SMPROGRAMS\${PRODUCT_NAME}_v${PRODUCT_VERSION}\Start Agent.lnk" "$INSTDIR\scripts\start_pandora_agent.bat" "" "$INSTDIR\pandora.ico"
  CreateShortCut "$SMPROGRAMS\${PRODUCT_NAME}_v${PRODUCT_VERSION}\Stop Agent.lnk" "$INSTDIR\scripts\stop_pandora_agent.bat" "" "$INSTDIR\pandora.ico"
SectionEnd

Section -Post
  WriteUninstaller "$INSTDIR\uninst.exe"
  WriteRegStr HKLM "${PRODUCT_DIR_REGKEY}" "" "$INSTDIR\PandoraAgent.exe"
  WriteRegStr ${PRODUCT_UNINST_ROOT_KEY} "${PRODUCT_UNINST_KEY}" "DisplayName" "Pandora Open Agent"
  WriteRegStr ${PRODUCT_UNINST_ROOT_KEY} "${PRODUCT_UNINST_KEY}" "UninstallString" "$INSTDIR\uninst.exe"
  WriteRegStr ${PRODUCT_UNINST_ROOT_KEY} "${PRODUCT_UNINST_KEY}" "DisplayIcon" "$INSTDIR\pandora.ico"
SectionEnd

Function modify_conf_file
    ; Ensure we are working with a fresh copy to avoid double-processing
    ClearErrors
    FileOpen $0 "$INSTDIR\pandora_agent.conf" r
    FileOpen $1 "$INSTDIR\pandora_agent.conf.tmp" w
    
    IfErrors mcf_done

    mcf_loop:
        FileRead $0 $2
        IfErrors mcf_close

        ; Search and replace
        ${StrRep} $2 "$2" "@@INST_DIR@@" "$INSTDIR"
        ${StrRep} $2 "$2" "@@SERVER_IP@@" "$serverIpConf"

        FileWrite $1 $2
        Goto mcf_loop

    mcf_close:
        FileClose $1
        FileClose $0
        Delete "$INSTDIR\pandora_agent.conf"
        Rename "$INSTDIR\pandora_agent.conf.tmp" "$INSTDIR\pandora_agent.conf"
    mcf_done:
FunctionEnd

; --- Uninstaller ---

Section Uninstall
  ExecWait 'net stop PandoraOpenAgent'
  ExecWait 'sc delete PandoraOpenAgent'
  nsExec::Exec 'schtasks /Delete /TN pandora_agent_restart /F'
  !insertmacro un.RemoveFromPath "$INSTDIR\util"
  RMDir /r "$INSTDIR"
  SetShellVarContext all
  RMDir /r "$SMPROGRAMS\${PRODUCT_NAME}_v${PRODUCT_VERSION}"
  DeleteRegKey ${PRODUCT_UNINST_ROOT_KEY} "${PRODUCT_UNINST_KEY}"
  DeleteRegKey HKLM "${PRODUCT_DIR_REGKEY}"
SectionEnd
