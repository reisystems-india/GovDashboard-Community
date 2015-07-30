def transposeRecordColumns2Rows(record, keyColumnNames, selectedNonkeyColumns, generatedColumnName4Enumeration, generatedColumnName4NonkeyColumns):
    """
    Transpose columns to rows
        Input:
            {c1: test1, c2: 1.17, c3: 0.13}
            {c1: test2,           c3: 0.23}
            {c1: test3, c2: 3.17}
        Output (keyColumnNames: [c1], selectedNonkeyColumns: None | [c2, c3], generatedColumnName4Enumeration: category, generatedColumnName4NonkeyColumns: value):
            {c1: test1, category: c2, value: 1.17}
            {c1: test1, category: c3, value: 0.13}
            {c1: test2, category: c3, value: 0.23}
            {c1: test3, category: c2, value: 3.17}
    """

    recordSubset4KeyColumnNames = dict()
    for columnName in keyColumnNames:
        recordSubset4KeyColumnNames[columnName] = record[columnName] if (columnName in record) else None

    transposedRecords = list()
    for columnName, value in record.iteritems():
        if (columnName in keyColumnNames):
            continue
        if (value is None):
            continue
        if ((selectedNonkeyColumns is not None) and (columnName not in selectedNonkeyColumns)):
            continue

        transposedRecord = recordSubset4KeyColumnNames.copy()
        transposedRecord[generatedColumnName4Enumeration] = columnName
        transposedRecord[generatedColumnName4NonkeyColumns] = value

        transposedRecords.append(transposedRecord)

    return transposedRecords


def transposeColumns2Rows(data, keyColumnNames, selectedNonkeyColumns, generatedColumnName4Enumeration, generatedColumnName4NonkeyColumns):
    if (data is None):
        return None

    transposedRecords = list()
    for record in data:
        transposedRecords.extend(transposeRecordColumns2Rows(record, keyColumnNames, selectedNonkeyColumns, generatedColumnName4Enumeration, generatedColumnName4NonkeyColumns))

    return transposedRecords


def transform2Table(data, selectedColumns = None):
    """
    Table formatter:
        Input:
            {c1: test1, c2: 10, c3: 0.13}
            {c1: test2,         c3: 0.23}
            {c1: test3, c2: 30, c3: 0.33}
            {c1: test4, None,   c3: 0.43}
        Output:
            [c1,    c2,   c3  ]
            [test1, 10,   0.13]
            [test2, None, 0.23]
            [test3, 30,   0.33]
            [test4, None, 0.43]
    """

    if (data is None):
        return None

    header = list()
    if (selectedColumns is not None):
        header.extend(selectedColumns)

    table = list()

    for record in data:
        if (selectedColumns is None):
            for columnName, value in record.iteritems():
                if (columnName not in header):
                    # registering new column
                    header.append(columnName)
                    # adding NULL values for new column for previous records
                    for r in table:
                        r.insert(index, None)

        tableRecord = list()
        for columnName in header:
            tableRecord.append(record[columnName] if (columnName in record) else None)
        table.append(tableRecord)

    table.insert(0, header)

    return table

def transform2Data(table):
    """
    Table to Data formatter:
        Input:
            [c1,    c2,   c3  ]
            [test1, 10,   0.13]
            [test2, None, 0.23]
            [test3, 30,   0.33]
            [test4, None, 0.43]
        Output:
            {c1: test1, c2: 10, c3: 0.13}
            {c1: test2,         c3: 0.23}
            {c1: test3, c2: 30, c3: 0.33}
            {c1: test4, None,   c3: 0.43}
    """

    if (table is None):
        return None

    data = list()

    header = table[0]
    for record in table[1:]:
        dataRecord = dict(zip(header, record))
        data.append(dataRecord)

    return data
