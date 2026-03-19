bin_PROGRAMS = PandoraAgent
if DEBUG
PandoraAgent_CXXFLAGS=-g -O0
else
PandoraAgent_CXXFLAGS=-O2
endif
PandoraAgent_SOURCES = misc/cron.cc misc/pandora_file.cc modules/pandora_data.cc modules/pandora_module_factory.cc modules/pandora_module.cc modules/pandora_module_list.cc modules/pandora_module_plugin.cc modules/pandora_module_fim.cc modules/pandora_module_inventory.cc modules/pandora_module_freememory.cc modules/pandora_module_exec.cc modules/pandora_module_exec_powershell.cc modules/pandora_module_perfcounter.cc modules/pandora_module_proc.cc modules/pandora_module_tcpcheck.cc modules/pandora_module_freememory_percent.cc modules/pandora_module_freedisk.cc modules/pandora_module_freedisk_percent.cc modules/pandora_module_logevent.cc modules/pandora_module_logchannel.cc modules/pandora_module_service.cc modules/pandora_module_cpuusage.cc modules/pandora_module_wmiquery.cc modules/pandora_module_regexp.cc modules/pandora_module_ping.cc modules/pandora_module_snmpget.cc udp_server/udp_server.cc main.cc pandora_strutils.cc pandora.cc windows_service.cc pandora_agent_conf.cc windows/pandora_windows_info.cc windows/pandora_wmi.cc pandora_windows_service.cc misc/md5.c windows/wmi/disphelper.c

.PHONY: clang-tidy cppcheck test fix

BASE_FLAGS = -I. -I./include -target x86_64-w64-mingw32
clang-tidy:
	@for file in $(PandoraAgent_SOURCES); do \
		echo "Linting $$file..."; \
		if [[ $$file == *.cc ]]; then \
			clang-tidy $$file -- -std=c++11 $(BASE_FLAGS); \
		fi; \
	done

cppcheck:
	@echo "Running cppcheck..."
	@cppcheck \
		-D_WIN32 \
		-I/usr/share/mingw-w64/include/ \
		-I./include \
		-I./modules \
		-I. \
		--enable=all \
		--inconclusive \
		--language=c++ \
		--library=std \
		--library=windows \
		--std=c++11 \
		--suppress=missingIncludeSystem \
		--suppress=preprocessorErrorDirective \
		--platform=win64 \
		$(PandoraAgent_SOURCES)

test: clang-tidy cppcheck
	@echo "All tests passed."

fmt:
	@for file in $(PandoraAgent_SOURCES); do \
		echo "Formatting $$file..."; \
		if [[ $$file == *.cc ]]; then \
			clang-tidy $$file --fix -- -std=c++11 $(BASE_FLAGS); \
		fi; \
	done

