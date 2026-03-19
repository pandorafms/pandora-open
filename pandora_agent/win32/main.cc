/* Pandora Windows agent main file.

   Copyright (c) 2006-2023 Pandora Open.
   Written by Esteban Sanchez.

   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2, or (at your option)
   any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License along
   with this program; if not, write to the Free Software Foundation,
   Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

#include <cstdlib>
#include <iostream>
#include "pandora.h"
#include "pandora_windows_service.h"

using namespace std;
using namespace Pandora;

#define PATH_SIZE                         _MAX_PATH+1
#define SERVICE_INSTALL_CMDLINE_PARAM    "--install"
#define SERVICE_UNINSTALL_CMDLINE_PARAM  "--uninstall"
#define HELP_CMDLINE_PARAM               "--help"
#define PROCESS_CMDLINE_PARAM            "--process"

int
main (int argc, char *argv[]) {
	Pandora_Windows_Service *service;
	char                     buffer[PATH_SIZE];
	string                   aux;
	int                      pos;
	bool                     process = false;
	string 					 home;
	string                   binary_path;

	service = Pandora_Windows_Service::getInstance ();
	service->setValues (Pandora::name, Pandora::display_name,
			    Pandora::description);
	service->start ();
	
	GetModuleFileName (NULL, buffer, MAX_PATH);
	aux = buffer;
	Pandora::setPandoraInstallPath (aux);
	pos = aux.rfind ("\\");
	aux.erase (pos + 1);
	Pandora::setPandoraInstallDir (aux);

	home = "PANDORA_HOME=";
	home += Pandora::getPandoraInstallDir ();
	putenv(home.c_str());

	/* Check the parameters */
	for (int i = 1; i < argc; i++) {
		if (_stricmp(argv[i], SERVICE_INSTALL_CMDLINE_PARAM) == 0) {
			/* Install parameter */

			/* Quote the path to the service binary to avoid exploits */
			binary_path = "\"";
			binary_path += Pandora::getPandoraInstallPath ().c_str ();
			binary_path += "\"";

			service->install (binary_path.c_str());
		
			delete service;
		
			return 0;
		} else if (_stricmp(argv[i], SERVICE_UNINSTALL_CMDLINE_PARAM) == 0) {
			/* Uninstall parameter */
			service->uninstall ();
		
			delete service;
		
			return 0;
		} else if (_stricmp(argv[i], HELP_CMDLINE_PARAM) == 0) {
			/* Help parameter */
			cout << "Pandora agent for Windows ";
			cout << "v" << getPandoraAgentVersion () << endl << endl;
			cout << "Usage: " << argv[0] << " [OPTION]" << endl << endl;
			cout << "Available options are:" << endl;
			cout << "\t" << SERVICE_INSTALL_CMDLINE_PARAM;
			cout <<	":  Install the Pandora Agent service." << endl;
			cout << "\t" << SERVICE_UNINSTALL_CMDLINE_PARAM;
			cout << ": Uninstall the Pandora Agent service." << endl;
			cout << "\t" << PROCESS_CMDLINE_PARAM;
			cout << ": Run the Pandora Agent as a user process instead of a service." << endl;
		
			return 0;
		} else if (_stricmp(argv[i], PROCESS_CMDLINE_PARAM) == 0) {
			process = true;
		} else {
			/* No parameter recognized */
			cout << "Pandora agent for Windows. ";
			cout << "Version " << getPandoraAgentVersion () << endl;
			cout << "Usage: " << argv[0] << " [" << SERVICE_INSTALL_CMDLINE_PARAM;
			cout << "] [" << SERVICE_UNINSTALL_CMDLINE_PARAM;
			cout << "] [" << PROCESS_CMDLINE_PARAM << "]";
			cout << endl << endl;
			cout << "Run " << argv[0] << " with " << HELP_CMDLINE_PARAM;
			cout << " parameter for more info." << endl;
		
			return 1;
		}
	}

#ifdef __DEBUG__
	service->pandora_init ();
	service->pandora_run ();
#else
	if (process) {
		cout << "Pandora agent is now running" << endl;
		service->pandora_init ();
		while (1) {
			service->pandora_run ();
			Sleep (service->interval);
		}
	} else {
		service->run ();
	}
#endif
	
	delete service;
	
	return 0;
}
