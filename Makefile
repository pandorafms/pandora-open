# Path to the source code
REPO_PATH ?= $(shell pwd)

# Output directory
BUILD_PATH ?= $(REPO_PATH)/build

# Extract version from Config.pm
VERSION := $(shell grep 'our $$VERSION =' $(REPO_PATH)/pandora_server/lib/PandoraOpen/Config.pm | awk '{print substr($$4, 2, length($$4) - 3)}')

# Windows build settings
HOST ?= x86_64-w64-mingw32
WIN32_DIR := $(REPO_PATH)/pandora_agent/win32
INSTALLER_NAME ?= PandoraOpenAgent_Setup-$(VERSION).exe

# VM name
VM_NAME ?= PandoraOpen

# Define the files where the version needs to be updated
VERSION_FILES = pandora_agent/unix/pandora_agent pandora_agent/win32/pandora.cc pandora_server/lib/PandoraOpen/Config.pm

# Targets
.PHONY: all clean console server agent_linux agent_windows test test_server test_agent_linux test_agent_win32 vm vm_clean vm_setup vm_install vm_shell vm_packages vm_transfer vm_reset vm_mount vm_umount

all: console server agent_linux

console: $(BUILD_PATH)/pandoraopen-console-$(VERSION).tar.gz

server: $(BUILD_PATH)/pandoraopen-server-$(VERSION).tar.gz

agent_linux: $(BUILD_PATH)/pandoraopen-agent-$(VERSION).tar.gz

agent_windows: $(BUILD_PATH)/$(INSTALLER_NAME)

# Ensure build directory exists
$(BUILD_PATH):
	@mkdir -p $(BUILD_PATH)
	@echo "Creating source tarballs in SOURCES")

# Console tarball
$(BUILD_PATH)/pandoraopen-console-$(VERSION).tar.gz: $(BUILD_PATH)
	@echo "Building console package..."
	cd $(REPO_PATH) && tar zcvf $(BUILD_PATH)/pandoraopen-console-$(VERSION).tar.gz --exclude \.git --exclude config.php pandora_console

# Server tarball
$(BUILD_PATH)/pandoraopen-server-$(VERSION).tar.gz: $(BUILD_PATH)
	@echo "Building server package..."
	cd $(REPO_PATH) && tar zcvf $(BUILD_PATH)/pandoraopen-server-$(VERSION).tar.gz --exclude \.git pandora_server

# Linux agent tarball
$(BUILD_PATH)/pandoraopen-agent-$(VERSION).tar.gz: $(BUILD_PATH)
	@echo "Building Linux agent package..."
	cd $(REPO_PATH)/pandora_agent && tar zcvf $(BUILD_PATH)/pandoraopen-agent-$(VERSION).tar.gz --exclude \.git unix

# Windows agent installer
$(BUILD_PATH)/$(INSTALLER_NAME): $(BUILD_PATH)
	@echo "Building Windows agent installer (version $(VERSION))..."
	@echo "Using cross-compiler: $(HOST)"
	
	# Build the Windows executable
	@echo "Step 1: Building PandoraAgent.exe..."
	@if [ ! -d "$(WIN32_DIR)" ]; then \
		echo "Error: Windows source directory $(WIN32_DIR) not found!"; \
		exit 1; \
	fi
	
	@cd $(WIN32_DIR) && \
	if [ -f build.sh ]; then \
		echo "Running build.sh script..."; \
		bash build.sh; \
	else \
		echo "Running autotools build process..."; \
		if [ ! -f configure ]; then \
			./autogen.sh; \
		fi; \
		./configure --host=$(HOST) && \
		make clean && \
		make && \
		cp PandoraAgent.exe bin/; \
	fi
	
	# Verify the executable was built
	@if [ ! -f "$(WIN32_DIR)/bin/PandoraAgent.exe" ]; then \
		echo "Error: PandoraAgent.exe was not built!"; \
		exit 1; \
	fi
	
	# Build the installer with NSIS
	@echo "Step 2: Creating Windows installer..."
	@if ! command -v makensis >/dev/null 2>&1; then \
		echo "Error: makensis not found. Install with: sudo apt-get install nsis"; \
		exit 1; \
	fi
	
	@makensis \
		-DPRODUCT_VERSION="$(VERSION)" \
		-DREPO_PATH="$(WIN32_DIR)" \
		-DFILE_NAME="$(BUILD_PATH)/$(INSTALLER_NAME)" \
		$(WIN32_DIR)/installer/pandora_open.nsi 2>&1 | grep -v "warning 6000\|warning 6001\|warning 6010"
	
	@if [ -f "$(BUILD_PATH)/$(INSTALLER_NAME)" ]; then \
		echo "✓ Windows installer created: $(BUILD_PATH)/$(INSTALLER_NAME)"; \
		ls -lh "$(BUILD_PATH)/$(INSTALLER_NAME)"; \
	else \
		echo "Error: Installer creation failed!"; \
		exit 1; \
	fi

# Main test target
test: test_server test_agent_linux test_agent_win32
	@echo "All tests completed."

# Server: Requires perl Makefile.PL first
test_server:
	@echo "Testing Pandora Server..."
	@cd $(REPO_PATH)/pandora_server && \
	if [ ! -f Makefile ]; then perl Makefile.PL; fi && \
	make test

# Linux Agent: Standard make test
test_agent_linux:
	@echo "Testing Linux Agent..."
	@$(MAKE) -C $(REPO_PATH)/pandora_agent/unix test

# Windows Agent: Requires autogen and configure
test_agent_win32:
	@echo "Testing Windows Agent..."
	@cd $(REPO_PATH)/pandora_agent/win32 && \
	if [ ! -f Makefile ]; then \
		./autogen.sh && ./configure --host=$(HOST); \
	fi && \
	make test

# Clean up
clean:
	@echo "Cleaning build directory..."
	rm -f $(BUILD_PATH)/*.tar.gz
	rm -f $(BUILD_PATH)/$(INSTALLER_NAME)
	@echo "Note: Windows build artifacts in $(WIN32_DIR) are not cleaned automatically."
	@echo "      To clean Windows build, run: cd $(WIN32_DIR) && make clean"

# VM targets
vm: vm_clean vm_setup vm_install
	@echo "VM setup and installation complete!"
	@echo ""
	@echo "To mount the console directory for local development, run: make vm_mount"
	@echo "To unmount, run: make vm_umount"

# Clean up VM
vm_clean:
	@echo "Checking for existing VM..."
	@if multipass list --format csv 2>/dev/null | grep -q "^$(VM_NAME),"; then \
		echo "VM '$(VM_NAME)' exists. Are you sure you want to delete it? (y/N)"; \
		read -r confirm; \
		if [ "$$confirm" != "y" ] && [ "$$confirm" != "Y" ]; then \
			echo "Aborted."; \
			exit 1; \
		fi; \
	fi
	@echo "Cleaning up existing VM..."
	-multipass stop $(VM_NAME) 2>/dev/null || true
	-multipass delete $(VM_NAME) 2>/dev/null || true
	-multipass purge 2>/dev/null || true

# Set up VM
vm_setup:
	@echo "Launching VM..."
	multipass launch jammy --name $(VM_NAME) --disk 30G --memory 4G
	@echo "Starting VM..."
	multipass start $(VM_NAME)
	@echo "Installing make in VM..."
	multipass exec $(VM_NAME) -- sudo apt-get update
	multipass exec $(VM_NAME) -- sudo apt-get install -y make

# Transfer code into VM (safe to run multiple times)
vm_transfer:
	@echo "Checking VM status..."
	@if ! multipass list --format csv 2>/dev/null | grep -q "^$(VM_NAME),.*,Running"; then \
		echo "VM is not running. Starting it now..."; \
		multipass start $(VM_NAME) || (echo "Failed to start VM. Does it exist?"; exit 1); \
	fi
	@echo "Clearing previous repo in VM..."
	multipass exec $(VM_NAME) -- rm -rf /tmp/pandoraopen
	@echo "Transferring code to VM..."
	multipass transfer -r $(REPO_PATH)/ $(VM_NAME):/tmp/pandoraopen/

# Reset PandoraOpen installation inside VM (safe for re-runs)
vm_reset:
	@echo "Resetting PandoraOpen installation in VM..."
	@echo "Stopping Pandora services..."
	-multipass exec $(VM_NAME) -- sudo systemctl stop pandora_server tentacle_serverd pandora_agent_daemon apache2 mysql mariadb mysqld 2>/dev/null || true
	@echo "Removing Pandora files..."
	-multipass exec $(VM_NAME) -- sudo rm -rf /var/www/html/pandora_console /etc/pandora /var/log/pandora /usr/bin/pandora_server /usr/bin/pandora_agent \
	    /usr/share/pandora_server /usr/share/pandora_agent /var/spool/pandora /var/spool/pandora/tmp/pandora_agent_daemon \
	    /var/lib/mysql/pandora 2>/dev/null || true
	-multipass exec $(VM_NAME) -- sudo rm -f /etc/init.d/pandora_server /etc/init.d/pandora_agent_daemon /etc/init.d/tentacle_serverd 2>/dev/null || true
	-multipass exec $(VM_NAME) -- sudo rm -f /etc/logrotate.d/pandora_server /etc/logrotate.d/pandora_agent 2>/dev/null || true
	@echo "Cleaning Pandora cron entries..."
	-multipass exec $(VM_NAME) -- sudo sed -i "/pandora_console\/cron\.php/d" /etc/crontab 2>/dev/null || true
	-multipass exec $(VM_NAME) -- sudo sed -i "/pandora_db/d" /etc/crontab 2>/dev/null || true

	@echo "Starting database for cleanup..."
	-multipass exec $(VM_NAME) -- bash -lc "sudo systemctl start mysql mariadb mysqld >/dev/null 2>&1 || true"
	@echo "Dropping Pandora database and user..."
	-multipass exec $(VM_NAME) -- bash -lc "sudo mysql --defaults-extra-file=/etc/mysql/debian.cnf -e \"DROP DATABASE IF EXISTS pandora; DROP USER IF EXISTS \'pandora\'@\'%\';\" >/dev/null 2>&1 || true"

# Build only the packages needed for VM deployment
vm_packages: vm_transfer
	@echo "Building console, server, and Linux agent packages in VM..."
	multipass exec $(VM_NAME) -- make -C /tmp/pandoraopen console server agent_linux

# Install PandoraOpen in VM
vm_install: vm_reset vm_packages
	@echo "Running deployment script from repo root..."
	multipass exec $(VM_NAME) -- bash -c "cd /tmp/pandoraopen && sudo bash extra/pandora_deploy.sh /tmp/pandoraopen/build"
	@echo "PandoraOpen installation complete in VM!"
	@echo ""
	@echo "The console is available at: http://<vm-ip>/pandora_console"
	@echo "Credentials: admin / pandora"
	@echo ""
	@echo "You can access the VM with: make vm_shell"
	@echo "To mount the console directory for local development, run: make vm_mount"

# Mount the console directory for local development
vm_mount:
	@echo "Checking VM status..."
	@if ! multipass list --format csv 2>/dev/null | grep -q "^$(VM_NAME),.*,Running"; then \
		echo "VM is not running. Starting it now..."; \
		multipass start $(VM_NAME) || (echo "Failed to start VM. Does it exist?"; exit 1); \
	fi
	@echo "Checking if the console is already mounted..."
	@if multipass info $(VM_NAME) | grep -q "$(REPO_PATH)/pandora_console"; then \
		echo "Console directory is already mounted."; \
		exit 0; \
	fi
	@echo "Detecting IDs..."
	$(eval VM_UID := $(shell multipass exec $(VM_NAME) -- id -u www-data))
	$(eval VM_GID := $(shell multipass exec $(VM_NAME) -- id -g www-data))
	$(eval HOST_UID := $(shell id -u))
	$(eval HOST_GID := $(shell id -g))
	@echo "Mapping Host($(HOST_UID):$(HOST_GID)) to VM www-data($(VM_UID):$(VM_GID))"
	@echo "Copying installed config.php..."
	multipass exec $(VM_NAME) -- sudo -n cat /var/www/html/pandora_console/include/config.php > "$(REPO_PATH)/pandora_console/include/config.php" 2>/dev/null || echo "Could not copy config.php"
	@echo "Stopping VM to mount directory..."
	-multipass stop $(VM_NAME)
	@echo "Mounting local directory with mapped IDs..."
	@# Format is HostID:VMID
	multipass mount -u $(HOST_UID):$(VM_UID) -g $(HOST_GID):$(VM_GID) "$(REPO_PATH)/pandora_console" $(VM_NAME):/var/www/html/pandora_console
	@echo "Starting VM..."
	multipass start $(VM_NAME)
	@echo "Setting permissions..."
	multipass exec $(VM_NAME) -- sudo chmod -R a+rX /var/www/html/pandora_console
	@echo "Restarting Apache..."
	multipass exec $(VM_NAME) -- sudo systemctl restart apache2
	@echo "Mount complete!"

# Unmount the console directory
vm_umount:
	@echo "Checking VM status..."
	@if ! multipass list --format csv 2>/dev/null | grep -q "^$(VM_NAME),.*,Running"; then \
		echo "VM is not running. Starting it now..."; \
		multipass start $(VM_NAME) || (echo "Failed to start VM. Does it exist?"; exit 1); \
	fi
	@echo "Checking if the console is mounted..."
	@if ! multipass info $(VM_NAME) | grep -q "$(REPO_PATH)/pandora_console"; then \
		echo "Console directory is not mounted."; \
		exit 0; \
	fi
	@echo "Stopping VM to unmount directory..."
	-multipass stop $(VM_NAME)
	@echo "Unmounting directory..."
	-multipass unmount $(VM_NAME):/var/www/html/pandora_console
	@echo "Starting VM..."
	multipass start $(VM_NAME)
	@echo "The original console installation is now visible again."
	@echo "Restarting Apache..."
	multipass exec $(VM_NAME) -- sudo systemctl restart apache2
	@echo "Unmount complete!"
	@echo "The console is now using the original installed files in the VM."
	@echo "Local directory $(REPO_PATH)/pandora_console is no longer connected to the VM."

# Shell into VM
vm_shell:
	@echo "Checking VM status..."
	@if ! multipass list --format csv 2>/dev/null | grep -q "^$(VM_NAME),.*,Running"; then \
		echo "VM is not running. Starting it now..."; \
		multipass start $(VM_NAME) || (echo "Failed to start VM. Does it exist?"; exit 1); \
	fi
	@echo "Opening shell in VM..."
	multipass shell $(VM_NAME)

# Local install
install:
	@echo "Running installation on local host..."
	@chmod +x extra/pandora_deploy.sh
	@echo "Running deployment script from repo root..."
	@sudo bash extra/pandora_deploy.sh $(BUILD_PATH)
	@echo "PandoraOpen installation complete on local host!"
	@echo ""
	@echo "The console is available at: http://localhost/pandora_console"
	@echo "Credentials: admin / pandora"

# Target to set the version
version:
	@if [ -z "$(VERSION)" ]; then \
		echo "Error: VERSION is not set. Use 'make version VERSION=x.y.z'"; \
		exit 1; \
	fi
	@echo "Setting version to $(VERSION)"
	@for file in $(VERSION_FILES); do \
		if [ "$$file" = "pandora_agent/unix/pandora_agent" ]; then \
			sed -i "s/use constant AGENT_VERSION => '.*';/use constant AGENT_VERSION => '$(VERSION)';/" $$file; \
		elif [ "$$file" = "pandora_agent/win32/pandora.cc" ]; then \
			sed -i "s/#define PANDORA_VERSION (\".*\")/#define PANDORA_VERSION (\"$(VERSION)\")/" $$file; \
		elif [ "$$file" = "pandora_server/lib/PandoraOpen/Config.pm" ]; then \
			sed -i "s/our \$$VERSION = \".*\";/our \$$VERSION = \"$(VERSION)\";/" $$file; \
		fi; \
	done
