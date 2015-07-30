
NAME_SPACE_SEPARATOR = ':'

def splitAlias(alias):
    parts = alias.split(NAME_SPACE_SEPARATOR, 1)

    return [None, parts[0]] if (len(parts) == 1) else parts
