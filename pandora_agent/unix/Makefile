SCRIPTS = pandora_agent pandora_agent_exec tentacle_client tentacle_server

all: test

# Test target
test: syntax critic

# Syntax check
syntax:
	@echo "--- Checking syntax with perl -c ---"
	@for script in $(SCRIPTS); do \
		perl -c $$script || exit 1; \
	done
	@echo "Syntax OK."

# Static analysis
critic:
	@echo "--- Analyzing code quality with perlcritic ---"
	perlcritic --profile .perlcriticrc $(SCRIPTS)

# Reformat the code
fmt:
	@echo "--- Automatically formatting code with perltidy ---"
	@for script in $(SCRIPTS); do \
		perltidy -b $$script; \
	done
	@echo "Formatting complete. Backup files (.bak) created."

# Clean up backup files created by perltidy
clean:
	@echo "--- Removing backup files ---"
	rm -f *.bak *.tdy

.PHONY: all test syntax critic fix clean
