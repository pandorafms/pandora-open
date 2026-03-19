use 5.000;
use ExtUtils::MakeMaker;

my %ARGV = map { my @r = split /=/,$_; defined $r[1] or $r[1]=1; @r } @ARGV;
my @exe_files = qw(bin/pandora_server bin/pandora_exec);
$ARGV{WITHOUT_TENTACLE} or push @exe_files, 'bin/tentacle_server';

WriteMakefile(
    INSTALLSITELIB  => '/usr/lib/perl5',
    (($^O eq 'freebsd')
        ? (INSTALLSITELIB => '') : ()
    ),
    ($^O eq 'netbsd') ? (
        INSTALLSITELIB    => '',
        INSTALLSITESCRIPT => '/usr/local/bin',
    ) :(),

    NAME         => 'PandoraOpen',
    VERSION_FROM => 'lib/PandoraOpen/Config.pm',
    AUTHOR       => 'Pandora Open <info@pandorafms.com>',
    PREREQ_PM    => {
        NetAddr::IP       => 0,
        DBI               => 0,
        threads::shared   => 0,
        IO::Socket        => 0,
        Socket6           => 0,
        Time::Local       => 0,
        XML::Simple       => 0,
        XML::Twig         => 0,
        Time::HiRes       => 0,
        HTML::Entities    => 0,
        IO::Socket::INET6 => 0,
        JSON              => 0,
    },
    EXE_FILES => [ @exe_files ],
    PMLIBDIRS => [ 'lib' ],
    'dist'    => { 'TAR' => 'tar', 'TARFLAGS' => 'cvfz', 'SUFFIX' => '.gz', 'COMPRESS' => 'gzip'}
);

sub MY::postamble {
    return <<'END_MAKE';

SCRIPTS = bin/pandora_server bin/tentacle_server
MODULES = $(wildcard lib/PandoraOpen/*.pm)

# Hook into the existing test target
test :: syntax critic

# Syntax check with automated mocking for missing dependencies
syntax:
	@echo "--- Creating mock environment ---"
	@mkdir -p mock_lib/NetAddr mock_lib/XML mock_lib/IO/Socket mock_lib/HTML
	@for mod in DBI.pm JSON.pm Socket6.pm \
		NetAddr/IP.pm XML/Simple.pm XML/Twig.pm \
		IO/Socket/INET6.pm HTML/Entities.pm; do \
		echo "1;" > mock_lib/$$mod; \
	done
	@echo "--- Checking syntax with perl -Ilib -Imock_lib -c ---"
	@for script in $(SCRIPTS); do \
		perl -Ilib -Imock_lib -c $$script || exit 1; \
	done
	@for module in $(MODULES); do \
		perl -Ilib -Imock_lib -c $$module || exit 1; \
	done
	@echo "Syntax OK."
	@echo "--- Cleaning up mock environment ---"
	@rm -rf mock_lib

# Static analysis
critic:
	@echo "--- Analyzing code quality with perlcritic ---"
	perlcritic --profile .perlcriticrc $(SCRIPTS) $(MODULES)

# Reformat the code
fmt:
	@echo "--- Automatically formatting code with perltidy ---"
	@for script in $(SCRIPTS); do \
		perltidy -b $$script; \
	done
	@for module in $(MODULES); do \
		perltidy -b $$module; \
	done
	@echo "Formatting complete. Backup files (.bak) created."

# Use double-colon to append to the existing clean target
clean ::
	@echo "--- Removing perltidy backup files ---"
	rm -f bin/*.bak lib/PandoraOpen/*.bak *.tdy
	rm -rf mock_lib

.PHONY: syntax critic fix
END_MAKE
}
