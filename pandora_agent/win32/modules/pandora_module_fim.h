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

#ifndef	__PANDORA_MODULE_FIM_H__
#define	__PANDORA_MODULE_FIM_H__

#include "pandora_module_plugin.h"
#include <string>

using namespace std;

namespace Pandora_Modules {
	/**
	 * Module to create and configure a File Integrity Monitoring (FIM) module.
	 * This module uses the pandora_fim.exe utility to monitor file changes.
	 */
	class Pandora_Module_FIM {
	public:
		static Pandora_Module* createFIMModule(long interval, long intensive_interval);
	};
}

#endif
