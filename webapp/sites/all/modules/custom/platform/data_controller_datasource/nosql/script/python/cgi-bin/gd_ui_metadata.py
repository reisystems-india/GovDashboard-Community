import dp_namespace
import dp_parameter

UI_NAME_SPACE__ATTRIBUTE = 'attr'
UI_NAME_SPACE__MEASURE = 'measure'

def mapUIColumnName(datasetName, uiColumnName):
    namespaceParts = dp_namespace.splitAlias(uiColumnName)
    namespace = namespaceParts[0]
    parameterName = namespaceParts[1]
    parameterNameParts = dp_parameter.splitName(parameterName)

    mappedColumnName = None
    if (namespace == UI_NAME_SPACE__ATTRIBUTE):
        dimensionName = parameterNameParts[0]
        columnName = parameterNameParts[1]
        if (columnName is None):
            mappedColumnName = dimensionName
    elif (namespace == UI_NAME_SPACE__MEASURE):
        mappedColumnName = parameterNameParts[0]
    else:
        raise ValueError('Invalid UI column name space: {namespace}'.format(namespace = namespace))

    if (mappedColumnName is None):
        raise ValueError('Unsupported UI column name: {uiColumnName}'.format(uiColumnName = uiColumnName))

    return mappedColumnName
