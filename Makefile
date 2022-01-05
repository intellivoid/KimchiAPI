clean:
	rm -rf build

build:
	mkdir build
	ppm --no-intro --cerror --compile="src/KimchiAPI" --directory="build"
	ppm --no-intro --cerror --compile="api_handler" --directory="build"

update:
	ppm --generate-package="src/KimchiAPI"
	ppm --generate-package="api_handler"

install:
	ppm --no-intro --no-prompt --fix-conflict --install="build/net.intellivoid.kimchi_api.ppm"
	ppm --no-intro --no-prompt --fix-conflict --install="build/net.intellivoid.test_api.ppm"

install_fast:
	ppm --no-intro --no-prompt --fix-conflict --skip-dependencies --install="build/net.intellivoid.kimchi_api.ppm"
	ppm --no-intro --no-prompt --fix-conflict --skip-dependencies --install="build/net.intellivoid.test_api.ppm"