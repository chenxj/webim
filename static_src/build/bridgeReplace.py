import sys,re

content = open(sys.argv[1]).read()
pattern = "ajax.*?else\{(\w+)\(.*?\)"
result = re.compile(pattern).findall(content)
content.replace("ajax",result[0])

