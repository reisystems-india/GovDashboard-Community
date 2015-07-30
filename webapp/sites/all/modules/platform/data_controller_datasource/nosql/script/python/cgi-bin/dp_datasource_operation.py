import inspect
import dp_api
import dp_qs_parameter
import dp_dataset_script
import dp_data_column
import dp_data_filtering
import dp_data_sorting
import dp_data_pagination


PARAMETER_NAME__CALLBACK_SERVER_NAME = 'callback'
PARAMETER_NAME__OPERATION = 'exec'

OPERATION_NAME__DATASET_COLUMN_DEFINITION = 'defineDatasetColumns'
OPERATION_NAME__DATASET_QUERY = 'queryDataset'
OPERATION_NAME__DATASET_RECORD_COUNT = 'countDatasetRecords'


def executeQueryDatasetFunction(functionRef, callbackServerName, datasetName, columns, filters, sort, offset, limit):
    MIN_ARGUMENT_COUNT = 2
    MAX_ARGUMENT_COUNT = 7

    supportedArgumentSpecs = inspect.getargspec(functionRef)
    supportedArgumentCount = len(supportedArgumentSpecs.args)
    if (supportedArgumentCount < MIN_ARGUMENT_COUNT):
        raise SystemError("'{funcName}' function definition has to have at least {minArgumentCount} argument(s)".format(funcName = OPERATION_NAME__DATASET_QUERY, minArgumentCount = MIN_ARGUMENT_COUNT))
    if (supportedArgumentCount > MAX_ARGUMENT_COUNT):
        raise SystemError("Incorrect number of arguments in '{funcName}' function definition".format(funcName = OPERATION_NAME__DATASET_QUERY))

    args = [callbackServerName, datasetName, columns, filters, sort, offset, limit][:supportedArgumentCount]

    isColumnSupported = supportedArgumentCount >= (MAX_ARGUMENT_COUNT - 4)
    isFilterSupported = supportedArgumentCount >= (MAX_ARGUMENT_COUNT - 3)
    isSortSupported = supportedArgumentCount >= (MAX_ARGUMENT_COUNT - 2)
    isOffsetSupported = supportedArgumentCount >= (MAX_ARGUMENT_COUNT - 1)
    isLimitSupported = supportedArgumentCount >= MAX_ARGUMENT_COUNT

    data = functionRef(*args)

    if (not isFilterSupported):
        dp_data_filtering.applyFilters(data, filters)
    if (not isSortSupported):
        dp_data_sorting.applySort(data, sort)
    if (isOffsetSupported):
        if (not isLimitSupported):
            dp_data_pagination.applyPagination(data, 0, limit)
    else:
        dp_data_pagination.applyPagination(data, offset, limit)
    if (not isColumnSupported):
        dp_data_column.retainColumnData(data, columns)

    return data


def execute():
    result = None

    # ----- parsing query parameters
    callbackServerName = dp_qs_parameter.parseParameterValue(PARAMETER_NAME__CALLBACK_SERVER_NAME, True)
    operation = dp_qs_parameter.parseParameterValue(PARAMETER_NAME__OPERATION, True)
    datasetName = dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__DATASET, True)
    datasetVersion = dp_dataset_script.parseVersion(dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__DATASET_VERSION, False))
    columns = dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__COLUMNS, False)
    filters = dp_data_filtering.parseFilters(dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__FILTERS, False))
    sort = dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__SORT, False)
    offset = dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__OFFSET, False, 0)
    limit = dp_qs_parameter.parseParameterValue(dp_api.PARAMETER_NAME__LIMIT, False)

    # ----- preparing dataset script module
    module = dp_dataset_script.accessScriptModule(callbackServerName, datasetName, datasetVersion)

    # ----- retrieving dataset data
    func = getattr(module, operation, None)
    if (operation == OPERATION_NAME__DATASET_COLUMN_DEFINITION):
        result = None if (func is None) else func(callbackServerName, datasetName)
    elif (operation == OPERATION_NAME__DATASET_QUERY):
        if (func is None):
            raise NameError('{functionName} function was not defined'.format(functionName = operation))
        result = executeQueryDatasetFunction(func, callbackServerName, datasetName, columns, filters, sort, offset, limit)
    elif (operation == OPERATION_NAME__DATASET_RECORD_COUNT):
        count = None if (func is None) else func(callbackServerName, datasetName, filters)
        if (count is None):
            funcQuery = getattr(module, OPERATION_NAME__DATASET_QUERY, None)
            if (funcQuery is None):
                if (func is None):
                    message = OPERATION_NAME__DATASET_QUERY + ' and ' + OPERATION_NAME__DATASET_RECORD_COUNT + ' functions were'
                else:
                    message = OPERATION_NAME__DATASET_QUERY + ' function was'
                message += ' not defined. Record count cannot be obtained'
                raise NameError(message)
            else:
                data = executeQueryDatasetFunction(funcQuery, callbackServerName, datasetName, dp_data_filtering.getFilterColumnNames(filters), filters, None, 0, None)
                count = 0 if (data is None) else len(data)
        result = count
    else:
        raise NameError("'{operation}' is not supported yet".format(operation = operation))

    return result
