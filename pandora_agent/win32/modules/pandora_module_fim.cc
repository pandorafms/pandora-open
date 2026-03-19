/* Pandora FIM module. File Integrity Monitoring module

   Copyright (c) 2006-2023 Pandora Open.

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

#include "pandora_module_fim.h"
#include "pandora_module_plugin.h"
#include "../pandora.h"
#include "../pandora_strutils.h"

using namespace std;
using namespace Pandora;
using namespace Pandora_Strutils;
using namespace Pandora_Modules;

/** 
 * Creates a FIM (File Integrity Monitoring) module.
 * 
 * @param interval Normal execution interval in milliseconds
 * @param intensive_interval Intensive execution interval in milliseconds
 * @return A pointer to the created Pandora_Module or NULL if creation failed
 */
Pandora_Module* 
Pandora_Module_FIM::createFIMModule(long interval, long intensive_interval) {
    string install_dir = Pandora::getPandoraInstallDir();

    string command = install_dir + "util/pandora_fim.exe";
	pandoraDebug("Executing FIM command: %s", command.c_str());

	Pandora_Modules::Pandora_Module *module = new Pandora_Modules::Pandora_Module_Plugin("FIM_Check", command);

    if (module != NULL) {
		pandoraDebug("Module created");
        module->setType("generic_data_string");
        module->setDescription("File integrity monitoring");

		string interval_str = inttostr(interval/1000);
		string intensive_interval_str = inttostr(intensive_interval/1000);

        if (!interval_str.empty() && !intensive_interval_str.empty()) {
            int normal_interval = atoi(interval_str.c_str());
            int intense_interval = atoi(intensive_interval_str.c_str());

            if (module->isIntensive()) {
                module->setIntensiveInterval(module->getInterval());
            } else {
                module->setIntensiveInterval(module->getInterval() * (normal_interval / intense_interval));
            }
        }

		module->setExecutions(module->getIntensiveInterval());
		module->setCron ("");
    }

    return module;
}
