PREFIX = .
STATIC_DIR = ${PREFIX}/static
STATIC_SRC_DIR = ${PREFIX}/static_src/webapi/dist

STATIC_SRC_FILES = ${STATIC_SRC_DIR}/images \
		   ${STATIC_SRC_DIR}/assets \
		   ${STATIC_SRC_DIR}/themes \
		   ${STATIC_SRC_DIR}/i18n 

all: static
	@@echo "complete."

static: 
	@@mkdir -p ${STATIC_DIR}
	@@cp ${STATIC_SRC_FILES} ${STATIC_DIR} -r
	@@cp ${STATIC_SRC_DIR}/webim.min.css ${STATIC_DIR}/webim.css
	@@cp ${STATIC_SRC_DIR}/webim.all.min.js ${STATIC_DIR}/webim.all.js

debug: 
	@@mkdir -p ${STATIC_DIR}
	@@cp ${STATIC_SRC_FILES} ${STATIC_DIR} -r
	@@cp ${STATIC_SRC_DIR}/webim.css ${STATIC_DIR}/webim.css
	@@cp ${STATIC_SRC_DIR}/webim.all.js ${STATIC_DIR}/webim.all.js
	@@echo "debug complete."

clean:
	@@echo "Removing Distribution directory:" ${STATIC_DIR}
	@@rm -rf ${STATIC_DIR}

