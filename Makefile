SRC_FILES = $(shell find example src -type f -name '*.php')

README.md: $(SRC_FILES)
	vendor/bin/mddoc

.PHONY: fix
fix:
	vendor/bin/php-cs-fixer fix
