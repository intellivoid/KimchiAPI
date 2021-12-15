clean:
	rm -rf build

build:
	mkdir build
	ppm --no-intro --cerror --compile="src/KimchiAPI" --directory="build"

update:
	ppm --generate-package="src/KimchiAPI"

install:
	ppm --no-intro --no-prompt --fix-conflict --install="build/net.intellivoid.kimchi_api.ppm"

install_fast:
	ppm --no-intro --no-prompt --fix-conflict --skip-dependencies --install="build/net.intellivoid.kimchi_api.ppm"