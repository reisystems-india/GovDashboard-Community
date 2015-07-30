
COLUMN_NAME_DELIMITER__CODE = '.'

def splitName(name):
    MIN_SECTION_COUNT = 1 # measure | column from facts dataset
    MAX_SECTION_COUNT = 2 # dimension [+ column]

    parts = name.split(COLUMN_NAME_DELIMITER__CODE)
    if (len(parts) > MAX_SECTION_COUNT):
        raise ValueError('Parameter name cannot contain more than {maxSectionCount} sections (dimension, column): {name}'.format(maxSectionCount = MAX_SECTION_COUNT, name = name))

    for i in range(0, (MAX_SECTION_COUNT - len(parts))):
        parts.append(None)

    return parts
