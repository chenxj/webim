PREFIX = .
STATIC_DIR = ${PREFIX}/static
STATIC_SRC_DIR = ${PREFIX}/static_src/dist

STATIC_SRC_FILES = ${STATIC_SRC_DIR}/images \
		   ${STATIC_SRC_DIR}/assets \
		   ${STATIC_SRC_DIR}/themes \
		   ${STATIC_SRC_DIR}/i18n 

all: submake static debug
	@@echo "complete."

submake:
	cd static_src && $(MAKE)
static: 
	@@mkdir -p ${STATIC_DIR}
	@@cp -r ${STATIC_SRC_FILES} ${STATIC_DIR} 

	@@cp ${STATIC_SRC_DIR}/webim.min.css ${STATIC_DIR}/webim.min.css
	@@cp ${STATIC_SRC_DIR}/webim_uc.min.css ${STATIC_DIR}/webim_uchome.min.css
	@@cp ${STATIC_SRC_DIR}/webim_dz.min.css ${STATIC_DIR}/webim_discuz.min.css

	@@cp ${STATIC_SRC_DIR}/webim.all.min.js ${STATIC_DIR}/webim.all.min.js
	@@cp ${STATIC_SRC_DIR}/webim_dz.all.min.js ${STATIC_DIR}/webim_discuz.all.min.js
	@@cp ${STATIC_SRC_DIR}/webim_uc.all.min.js ${STATIC_DIR}/webim_uchome.all.min.js

debug:  
	@@mkdir -p ${STATIC_DIR}
	@@cp -r ${STATIC_SRC_FILES} ${STATIC_DIR} 
	@@cp ${STATIC_SRC_DIR}/webim.css ${STATIC_DIR}/webim.all.css
	@@cp ${STATIC_SRC_DIR}/webim_uc.css ${STATIC_DIR}/webim_uchome.all.css
	@@cp ${STATIC_SRC_DIR}/webim_dz.css ${STATIC_DIR}/webim_discuz.all.css

	@@cp ${STATIC_SRC_DIR}/webim.all.js ${STATIC_DIR}/webim.all.js
	@@cp ${STATIC_SRC_DIR}/webim_uc.all.js ${STATIC_DIR}/webim_uchome.all.js
	@@cp ${STATIC_SRC_DIR}/webim_dz.all.js ${STATIC_DIR}/webim_discuz.all.js
	@@echo "debug complete."

clean:
	find ./ -name  "dist" | xargs rm -rf
	@@echo "Removing Distribution directory:" ${STATIC_DIR}
	@@rm -rf ${STATIC_DIR}

