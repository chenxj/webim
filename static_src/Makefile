PREFIX = .

BUILD_DIR = ${PREFIX}/im/build
DOCS_DIR = ${PREFIX}/docs
DIST_DIR = ${PREFIX}/dist

SRC_DIR = ${PREFIX}
CSS_SRC_DIR = ${SRC_DIR}/css
JS_SRC_DIR = ${SRC_DIR}/js
I18N_SRC_DIR = ${SRC_DIR}/js/i18n

IMAGE_SRC_DIR = ${SRC_DIR}/images
THEME_SRC_DIR = ${SRC_DIR}/themes
ASSET_SRC_DIR = ${SRC_DIR}/assets

CSS_BASE_FILES = ${CSS_SRC_DIR}/core.css\
	${CSS_SRC_DIR}/ui.core.css\
	${CSS_SRC_DIR}/icons.css\
	${CSS_SRC_DIR}/emot.css\
	${CSS_SRC_DIR}/chatlink.css\
	${CSS_SRC_DIR}/notification.css\
	${CSS_SRC_DIR}/setting.css\
	${CSS_SRC_DIR}/buddy.css\
	${CSS_SRC_DIR}/chat.css\
	${CSS_SRC_DIR}/menu.css\
	${CSS_SRC_DIR}/history.css\
	${CSS_SRC_DIR}/layout.css\
	${CSS_SRC_DIR}/hotpost.css\


IM = ${SRC_DIR}/im/dist/webim.js

JS_BASE_FILES = ${JS_SRC_DIR}/core.js\
	${JS_SRC_DIR}/date.js\
	${JS_SRC_DIR}/notice.js\
	${JS_SRC_DIR}/i18n.js\
	${JS_SRC_DIR}/ui.js\
	${JS_SRC_DIR}/ui.window.js\
	${JS_SRC_DIR}/ui.layout.js\
	${JS_SRC_DIR}/ui.emot.js\
	${JS_SRC_DIR}/ui.chatlink.js\
	${JS_SRC_DIR}/ui.setting.js\
	${JS_SRC_DIR}/ui.buddy.js\
	${JS_SRC_DIR}/ui.notification.js\
	${JS_SRC_DIR}/ui.history.js\
	${JS_SRC_DIR}/ui.menu.js\
	${JS_SRC_DIR}/ui.chat.js\
	${JS_SRC_DIR}/ui.hotpost.js\

UI_CSS_FILES = ${CSS_BASE_FILES}

UI_JS_FILES = ${JS_SRC_DIR}/intro.js\
	${JS_BASE_FILES}\
	${JS_SRC_DIR}/outro.js

UI_ALL_JS_FILES = ${IM}\
	${UI_JS_FILES}\

UI_VER = `cat ${SRC_DIR}/version.txt`
#DATE=`svn info . | grep Date: | sed 's/.*: //g'`
#REV=`svn info . | grep Rev: | sed 's/.*: //g'`
DATE=`git log -n 1 | grep Date: | sed 's/.*: //g'`
REV=`git log -n 1 | grep Rev: | sed 's/.*: //g'`

REPLACE = sed 's/Date:./&'"${DATE}"'/' | \
		sed 's/Revision:./&'"${REV}"'/' | \
		sed s/@VERSION/${UI_VER}/

#REPLACE = sed s/@VERSION/${UI_VER}/

MINJAR = java -jar ${BUILD_DIR}/yuicompressor-2.4.2.jar
UNICODE = native2ascii -encoding utf-8 

UI_CSS = ${DIST_DIR}/webim.css
UI_JS = ${DIST_DIR}/webim.ui.js
UI_ALL_JS = ${DIST_DIR}/webim.all.js

UI_MIN_CSS = ${DIST_DIR}/webim.min.css
UI_MIN_JS = ${DIST_DIR}/webim.ui.min.js
UI_MIN_ALL_JS = ${DIST_DIR}/webim.all.min.js


all: dist copy min i18n 
	@@echo "webim build complete."

dist:
	@@mkdir -p ${DIST_DIR}

	@@cat ${UI_CSS_FILES} | \
		sed 's/\.\.\/images/images/' | \
	       ${REPLACE} > ${UI_CSS};

	@@cat ${UI_ALL_JS_FILES} | \
	       ${REPLACE} > ${UI_ALL_JS};

	@@echo "Built"

copy:
	@@cp -r ${IMAGE_SRC_DIR} ${DIST_DIR}/images 
	@@cp -r ${ASSET_SRC_DIR} ${DIST_DIR}/assets 
	@@mkdir -p ${DIST_DIR}/themes
	@@cp -r ${THEME_SRC_DIR}/* ${DIST_DIR}/themes/
	@@echo "Copy images assets themes."

min:
	@@echo "Building"
	@@echo " - Compressing using Minifier"

	@@${MINJAR} --type css ${UI_CSS} > ${UI_MIN_CSS}
	@@echo ${UI_MIN_CSS} "Built"

	@@${MINJAR} --type js ${UI_ALL_JS} > ${UI_MIN_ALL_JS}
	@@echo ${UI_MIN_ALL_JS} "Built"
	@@echo

i18n:
	@@mkdir -p ${DIST_DIR}/i18n
	@@${UNICODE} ${I18N_SRC_DIR}/webim-zh-CN.js > ${DIST_DIR}/i18n/webim-zh-CN.js
	@@${UNICODE} ${I18N_SRC_DIR}/webim-zh-TW.js > ${DIST_DIR}/i18n/webim-zh-TW.js
	@@${UNICODE} ${I18N_SRC_DIR}/webim-en.js > ${DIST_DIR}/i18n/webim-en.js
	@@echo "i18n complete."

clean:
	@@echo "Removing Distribution directory:" ${DIST_DIR}
	@@rm -rf ${DIST_DIR}

