#!/usr/bin/env python
#-*- coding:utf-8 -*-

#-------------------------------------------
# Designer  : free.Won <freefis@Gmail.com>  
# Licence   : License on GPL Licence
# Archieved : Mar 2nd 2009  
#-------------------------------------------



import os
import commands
import simplejson

ROOT = os.path.dirname( os.path.dirname( os.path.abspath(__file__) ) )



def get_all_file(dir):
    """ """
    all_file_hash = {}
    print "walking whole dir..."
    for root, dirs, files in os.walk(dir):
        for name in files:
            abs_path = os.path.join(root, name)

            if os.path.basename(abs_path) == "config.php":
                continue
            if os.path.getsize(abs_path) == 0:
                continue

            ins_path = "webim/" + (abs_path.split("webim/"))[1]
            md5 = ( commands.getoutput("md5sum " + abs_path).split(" ") )[0]
            all_file_hash[ins_path] = {"abs_path":abs_path,"md5":md5} 
    return all_file_hash


def make_index(content,dir):
    Path =os.path.join(dir,"update/file_index")
    print Path
    handle = open(Path,"wb")
    handle.write(content)
    handle.close()

def make_version(content,dir):
    Path =os.path.join(dir,"update/version")
    handle = open(Path,"wb")
    handle.write(content)
    handle.close()


if __name__ == '__main__':
    version = "2.2.2"
    import sys
    path = sys.argv[1]
    if not path.startswith("./"):
        webimpath = "./" + path
    else:
        webimpath = path
    all_file_hash = get_all_file(webimpath)
    Json = simplejson.dumps(all_file_hash)
    make_index(Json,webimpath)
    make_version(version,webimpath)
