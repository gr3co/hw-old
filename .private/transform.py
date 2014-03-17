import json

old_file = open("helloworld_base.json", "r")
contents = old_file.read()
my_json = json.loads(contents)
num = 0

for x in my_json:
    new_file = open("helloworld_base_transformed" + str(num) + ".json", "w")
    json.dump(x, new_file)
    num = num + 1
