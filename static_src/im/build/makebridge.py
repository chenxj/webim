import sys,re

content = open(sys.argv[1]).read()
pattern = "\(function\(\w+,\w+,\w+\){(.*)function.*?\"webim_bridge_separator\""
result = re.compile(pattern,re.DOTALL).findall(content)
print result[0]

