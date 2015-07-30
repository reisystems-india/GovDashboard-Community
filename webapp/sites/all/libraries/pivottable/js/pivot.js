(function() {
  var callWithJQuery,
    __indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; },
    __slice = [].slice,
    __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; },
    __hasProp = {}.hasOwnProperty;

  callWithJQuery = function(pivotModule) {
    if (typeof exports === "object" && typeof module === "object") {
      return pivotModule(require("jquery"));
    } else if (typeof define === "function" && define.amd) {
      return define(["jquery"], pivotModule);
    } else {
      return pivotModule(jQuery);
    }
  };

  callWithJQuery(function($) {

    /*
    Utilities
     */
    var rowMaxed, colMaxed, rowCount, colCount, currentRowPage = 1, currentColPage = 1, PivotData, addSeparators, aggregatorTemplates, aggregators, dayNamesEn, derivers, locales, mthNamesEn, naturalSort, numberFormat, pivotTableRenderer, renderers, usFmt, usFmtInt, usFmtPct, zeroPad;
    addSeparators = function(nStr, thousandsSep, decimalSep) {
      var rgx, x, x1, x2;
      nStr += '';
      x = nStr.split('.');
      x1 = x[0];
      x2 = x.length > 1 ? decimalSep + x[1] : '';
      rgx = /(\d+)(\d{3})/;
      while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + thousandsSep + '$2');
      }
      return x1 + x2;
    };
    numberFormat = function(opts) {
      var defaults;
      defaults = {
        digitsAfterDecimal: 2,
        scaler: 1,
        thousandsSep: ",",
        decimalSep: ".",
        prefix: "",
        suffix: "",
        showZero: false
      };
      opts = $.extend(defaults, opts);
      return function(x) {
        var result;
        if (isNaN(x) || !isFinite(x)) {
          return "";
        }
        if (x === 0 && !opts.showZero) {
          return "";
        }
        result = addSeparators((opts.scaler * x).toFixed(opts.digitsAfterDecimal), opts.thousandsSep, opts.decimalSep);
        return "" + opts.prefix + result + opts.suffix;
      };
    };
    usFmt = numberFormat();
    usFmtInt = numberFormat({
      digitsAfterDecimal: 0
    });
    usFmtPct = numberFormat({
      digitsAfterDecimal: 1,
      scaler: 100,
      suffix: "%"
    });
    aggregatorTemplates = {
      count: function(formatter) {
        if (formatter == null) {
          formatter = usFmtInt;
        }
        return function() {
          return function(data, rowKey, colKey) {
            return {
              count: 0,
              push: function() {
                return this.count++;
              },
              value: function() {
                return this.count;
              },
              format: formatter
            };
          };
        };
      },
      countUnique: function(formatter) {
        if (formatter == null) {
          formatter = usFmtInt;
        }
        return function(_arg) {
          var attr;
          attr = _arg[0];
          return function(data, rowKey, colKey) {
            return {
              uniq: [],
              push: function(record) {
                var _ref;
                if (_ref = record[attr], __indexOf.call(this.uniq, _ref) < 0) {
                  return this.uniq.push(record[attr]);
                }
              },
              value: function() {
                return this.uniq.length;
              },
              format: formatter,
              numInputs: attr != null ? 0 : 1
            };
          };
        };
      },
      listUnique: function(sep) {
        return function(_arg) {
          var attr;
          attr = _arg[0];
          return function(data, rowKey, colKey) {
            return {
              uniq: [],
              push: function(record) {
                var _ref;
                if (_ref = record[attr], __indexOf.call(this.uniq, _ref) < 0) {
                  return this.uniq.push(record[attr]);
                }
              },
              value: function() {
                return this.uniq.join(sep);
              },
              format: function(x) {
                return x;
              },
              numInputs: attr != null ? 0 : 1
            };
          };
        };
      },
      sum: function(formatter) {
        if (formatter == null) {
          formatter = usFmt;
        }
        return function(_arg) {
          var attr;
          attr = _arg[0];
          return function(data, rowKey, colKey) {
            return {
              sum: 0,
              push: function(record) {
                if (!isNaN(parseFloat(record[attr]))) {
                  return this.sum += parseFloat(record[attr]);
                }
              },
              value: function() {
                return this.sum;
              },
              format: formatter,
              numInputs: attr != null ? 0 : 1
            };
          };
        };
      },
      average: function(formatter) {
        if (formatter == null) {
          formatter = usFmt;
        }
        return function(_arg) {
          var attr;
          attr = _arg[0];
          return function(data, rowKey, colKey) {
            return {
              sum: 0,
              len: 0,
              push: function(record) {
                if (!isNaN(parseFloat(record[attr]))) {
                  this.sum += parseFloat(record[attr]);
                  return this.len++;
                }
              },
              value: function() {
                return this.sum / this.len;
              },
              format: formatter,
              numInputs: attr != null ? 0 : 1
            };
          };
        };
      },
      sumOverSum: function(formatter) {
        if (formatter == null) {
          formatter = usFmt;
        }
        return function(_arg) {
          var denom, num;
          num = _arg[0], denom = _arg[1];
          return function(data, rowKey, colKey) {
            return {
              sumNum: 0,
              sumDenom: 0,
              push: function(record) {
                if (!isNaN(parseFloat(record[num]))) {
                  this.sumNum += parseFloat(record[num]);
                }
                if (!isNaN(parseFloat(record[denom]))) {
                  return this.sumDenom += parseFloat(record[denom]);
                }
              },
              value: function() {
                return this.sumNum / this.sumDenom;
              },
              format: formatter,
              numInputs: (num != null) && (denom != null) ? 0 : 2
            };
          };
        };
      },
      sumOverSumBound80: function(upper, formatter) {
        if (upper == null) {
          upper = true;
        }
        if (formatter == null) {
          formatter = usFmt;
        }
        return function(_arg) {
          var denom, num;
          num = _arg[0], denom = _arg[1];
          return function(data, rowKey, colKey) {
            return {
              sumNum: 0,
              sumDenom: 0,
              push: function(record) {
                if (!isNaN(parseFloat(record[num]))) {
                  this.sumNum += parseFloat(record[num]);
                }
                if (!isNaN(parseFloat(record[denom]))) {
                  return this.sumDenom += parseFloat(record[denom]);
                }
              },
              value: function() {
                var sign;
                sign = upper ? 1 : -1;
                return (0.821187207574908 / this.sumDenom + this.sumNum / this.sumDenom + 1.2815515655446004 * sign * Math.sqrt(0.410593603787454 / (this.sumDenom * this.sumDenom) + (this.sumNum * (1 - this.sumNum / this.sumDenom)) / (this.sumDenom * this.sumDenom))) / (1 + 1.642374415149816 / this.sumDenom);
              },
              format: formatter,
              numInputs: (num != null) && (denom != null) ? 0 : 2
            };
          };
        };
      },
      fractionOf: function(wrapped, type, formatter) {
        if (type == null) {
          type = "total";
        }
        if (formatter == null) {
          formatter = usFmtPct;
        }
        return function() {
          var x;
          x = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
          return function(data, rowKey, colKey) {
            return {
              selector: {
                total: [[], []],
                row: [rowKey, []],
                col: [[], colKey]
              }[type],
              inner: wrapped.apply(null, x)(data, rowKey, colKey),
              push: function(record) {
                return this.inner.push(record);
              },
              format: formatter,
              value: function() {
                return this.inner.value() / data.getAggregator.apply(data, this.selector).inner.value();
              },
              numInputs: wrapped.apply(null, x)().numInputs
            };
          };
        };
      }
    };
    aggregators = (function(tpl) {
      return {
        "Count": tpl.count(usFmtInt),
        "Count Unique Values": tpl.countUnique(usFmtInt),
        "List Unique Values": tpl.listUnique(", "),
        "Sum": tpl.sum(usFmt),
        "Integer Sum": tpl.sum(usFmtInt),
        "Average": tpl.average(usFmt),
        "Sum over Sum": tpl.sumOverSum(usFmt),
        "80% Upper Bound": tpl.sumOverSumBound80(true, usFmt),
        "80% Lower Bound": tpl.sumOverSumBound80(false, usFmt),
        "Sum as Fraction of Total": tpl.fractionOf(tpl.sum(), "total", usFmtPct),
        "Sum as Fraction of Rows": tpl.fractionOf(tpl.sum(), "row", usFmtPct),
        "Sum as Fraction of Columns": tpl.fractionOf(tpl.sum(), "col", usFmtPct),
        "Count as Fraction of Total": tpl.fractionOf(tpl.count(), "total", usFmtPct),
        "Count as Fraction of Rows": tpl.fractionOf(tpl.count(), "row", usFmtPct),
        "Count as Fraction of Columns": tpl.fractionOf(tpl.count(), "col", usFmtPct)
      };
    })(aggregatorTemplates);
    renderers = {
      "Table": function(pvtData, opts) {
        return pivotTableRenderer(pvtData, opts);
      }
    };
    locales = {
      en: {
        aggregators: aggregators,
        renderers: renderers,
        localeStrings: {
          renderError: "An error occurred rendering the PivotTable results.",
          computeError: "An error occurred computing the PivotTable results.",
          uiRenderError: "An error occurred rendering the PivotTable UI.",
          selectAll: "Select All",
          selectNone: "Select None",
          tooMany: "(too many to list)",
          filterResults: "Filter results",
          totals: "Totals",
          vs: "vs",
          by: "by"
        }
      }
    };
    mthNamesEn = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    dayNamesEn = ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"];
    zeroPad = function(number) {
      return ("0" + number).substr(-2, 2);
    };
    derivers = {
      bin: function(col, binWidth) {
        return function(record) {
          return record[col] - record[col] % binWidth;
        };
      },
      dateFormat: function(col, formatString, mthNames, dayNames) {
        if (mthNames == null) {
          mthNames = mthNamesEn;
        }
        if (dayNames == null) {
          dayNames = dayNamesEn;
        }
        return function(record) {
          var date;
          date = new Date(Date.parse(record[col]));
          if (isNaN(date)) {
            return "";
          }
          return formatString.replace(/%(.)/g, function(m, p) {
            switch (p) {
              case "y":
                return date.getFullYear();
              case "m":
                return zeroPad(date.getMonth() + 1);
              case "n":
                return mthNames[date.getMonth()];
              case "d":
                return zeroPad(date.getDate());
              case "w":
                return dayNames[date.getDay()];
              case "x":
                return date.getDay();
              case "H":
                return zeroPad(date.getHours());
              case "M":
                return zeroPad(date.getMinutes());
              case "S":
                return zeroPad(date.getSeconds());
              default:
                return "%" + p;
            }
          });
        };
      }
    };
    naturalSort = (function(_this) {
      return function(as, bs) {
        var a, a1, b, b1, rd, rx, rz;
        rx = /(\d+)|(\D+)/g;
        rd = /\d/;
        rz = /^0/;
        if (typeof as === "number" || typeof bs === "number") {
          if (isNaN(as)) {
            return 1;
          }
          if (isNaN(bs)) {
            return -1;
          }
          return as - bs;
        }
        a = String(as).toLowerCase();
        b = String(bs).toLowerCase();
        if (a === b) {
          return 0;
        }
        if (!(rd.test(a) && rd.test(b))) {
          return (a > b ? 1 : -1);
        }
        a = a.match(rx);
        b = b.match(rx);
        while (a.length && b.length) {
          a1 = a.shift();
          b1 = b.shift();
          if (a1 !== b1) {
            if (rd.test(a1) && rd.test(b1)) {
              return a1.replace(rz, ".0") - b1.replace(rz, ".0");
            } else {
              return (a1 > b1 ? 1 : -1);
            }
          }
        }
        return a.length - b.length;
      };
    })(this);
    $.pivotUtilities = {
      aggregatorTemplates: aggregatorTemplates,
      aggregators: aggregators,
      renderers: renderers,
      derivers: derivers,
      locales: locales,
      naturalSort: naturalSort,
      numberFormat: numberFormat
    };

    /*
    Data Model class
     */
    PivotData = (function() {
      function PivotData(input, opts) {
        this.getRowKeys = __bind(this.getRowKeys, this);
        this.getColKeys = __bind(this.getColKeys, this);
        this.sortKeys = __bind(this.sortKeys, this);
        this.arrSort = __bind(this.arrSort, this);
        this.natSort = __bind(this.natSort, this);
        this.aggregator = opts.aggregator;
        this.colAttrs = opts.cols;
        this.rowAttrs = opts.rows;
        this.valAttrs = opts.vals;
        this.tree = {};
        this.rowKeys = [];
        this.colKeys = [];
        this.rowTotals = {};
        this.colTotals = {};
        this.allTotal = [];
        this.sorted = false;
        PivotData.forEachRecord(input, opts.derivedAttributes, (function(_this) {
          return function(record) {
            if (opts.filter(record)) {
              return _this.processRecord(record);
            }
          };
        })(this));
      }

      PivotData.forEachRecord = function(input, derivedAttributes, f) {
        var addRecord, compactRecord, i, j, k, record, tblCols, _i, _len, _ref, _results, _results1;
        if ($.isEmptyObject(derivedAttributes)) {
          addRecord = f;
        } else {
          addRecord = function(record) {
            var k, v, _ref;
            for (k in derivedAttributes) {
              v = derivedAttributes[k];
              record[k] = (_ref = v(record)) != null ? _ref : record[k];
            }
            return f(record);
          };
        }
        if ($.isFunction(input)) {
          return input(addRecord);
        } else if ($.isArray(input)) {
          if ($.isArray(input[0])) {
            _results = [];
            for (i in input) {
              if (!__hasProp.call(input, i)) continue;
              compactRecord = input[i];
              if (!(i > 0)) {
                continue;
              }
              record = {};
              _ref = input[0];
              for (j in _ref) {
                if (!__hasProp.call(_ref, j)) continue;
                k = _ref[j];
                record[k] = compactRecord[j];
              }
              _results.push(addRecord(record));
            }
            return _results;
          } else {
            _results1 = [];
            for (_i = 0, _len = input.length; _i < _len; _i++) {
              record = input[_i];
              _results1.push(addRecord(record));
            }
            return _results1;
          }
        } else if (input instanceof jQuery) {
          tblCols = [];
          $("thead > tr > th", input).each(function(i) {
            return tblCols.push($(this).text());
          });
          return $("tbody > tr", input).each(function(i) {
            record = {};
            $("td", this).each(function(j) {
              return record[tblCols[j]] = $(this).text();
            });
            return addRecord(record);
          });
        } else {
          throw new Error("unknown input format");
        }
      };

      PivotData.convertToArray = function(input) {
        if (!input) {
          input = [];
        }
        var result;
        result = [];
        PivotData.forEachRecord(input, {}, function(record) {
          return result.push(record);
        });
        return result;
      };

      PivotData.prototype.natSort = function(as, bs) {
        return naturalSort(as, bs);
      };

      PivotData.prototype.arrSort = function(a, b) {
        return this.natSort(a.join(), b.join());
      };

      PivotData.prototype.sortKeys = function() {
        if (!this.sorted) {
          this.rowKeys.sort(this.arrSort);
          this.colKeys.sort(this.arrSort);
        }
        return this.sorted = true;
      };

      PivotData.prototype.getColKeys = function() {
        this.sortKeys();
        return this.colKeys;
      };

      PivotData.prototype.getRowKeys = function() {
        this.sortKeys();
        return this.rowKeys;
      };

      PivotData.prototype.processRecord = function(record) {
        var colKey, flatColKey, flatRowKey, rowKey, x, _i, _j, _len, _len1, _ref, _ref1, _ref2, _ref3;
        colKey = [];
        rowKey = [];
        _ref = this.colAttrs;
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          x = _ref[_i];
          colKey.push((_ref1 = record[x]) != null ? _ref1 : "null");
        }
        _ref2 = this.rowAttrs;
        for (_j = 0, _len1 = _ref2.length; _j < _len1; _j++) {
          x = _ref2[_j];
          rowKey.push((_ref3 = record[x]) != null ? _ref3 : "null");
        }
        flatRowKey = rowKey.join(String.fromCharCode(0));
        flatColKey = colKey.join(String.fromCharCode(0));
        this.allTotal.push(record);
        if (rowKey.length !== 0) {
          if (!this.rowTotals[flatRowKey]) {
            this.rowKeys.push(rowKey);
            this.rowTotals[flatRowKey] = this.aggregator(this, rowKey, []);
          }
          this.rowTotals[flatRowKey].push(record);
        }
        if (colKey.length !== 0) {
          if (!this.colTotals[flatColKey]) {
            this.colKeys.push(colKey);
            this.colTotals[flatColKey] = this.aggregator(this, [], colKey);
          }
          this.colTotals[flatColKey].push(record);
        }
        if (colKey.length !== 0 && rowKey.length !== 0) {
          if (!this.tree[flatRowKey]) {
            this.tree[flatRowKey] = {};
          }
          if (!this.tree[flatRowKey][flatColKey]) {
            this.tree[flatRowKey][flatColKey] = this.aggregator(this, rowKey, colKey);
          }
          return this.tree[flatRowKey][flatColKey].push(record);
        }
      };

      return PivotData;

    })();

    /*
    Default Renderer for hierarchical table layout
     */
    pivotTableRenderer = function(pivotData, opts) {
      var lookupCell, lookupColumn, lookupRow, lookupTotal, colAttrs, colKey, colKeys, defaults, i, result, rowAttrs, rowKey, rowKeys, spanSize, td, th, tr, txt, val, x, container;
      defaults = {
        localeStrings: {
          totals: "Totals"
        }
      };
      opts = $.extend(defaults, opts);
      colAttrs = pivotData.colAttrs;
      rowAttrs = pivotData.rowAttrs;
      rowKeys = pivotData.getRowKeys();
      colKeys = pivotData.getColKeys();
      container = document.createElement("div");
      result = document.createElement("table");
      result.className = "pvtTable";
      lookupCell = opts.lookupCell;
      lookupColumn = opts.lookupColumn;
      lookupRow = opts.lookupRow;
      lookupTotal = opts.lookupTotal;
      spanSize = function(arr, i, j, page, limit) {
        var len, noDraw, stop, x, _i, _j;
        if (i !== 0) {
          noDraw = true;
          for (x = _i = 0; 0 <= j ? _i <= j : _i >= j; x = 0 <= j ? ++_i : --_i) {
            if (arr[i - 1][x] !== arr[i][x] || (i === (page-1)*limit)) {
              noDraw = false;
            }
          }
          if (noDraw) {
            return -1;
          }
        }
        len = 0;
        while (i + len < arr.length) {
          stop = false;
          for (x = _j = 0; 0 <= j ? _j <= j : _j >= j; x = 0 <= j ? ++_j : --_j) {
            if (arr[i][x] !== arr[i + len][x]) {
              stop = true;
            }
          }
          if (stop) {
            break;
          }
          len++;
        }
        return len;
      };
      colMaxed = false;
      rowMaxed = false;
      $.each(colAttrs, function(j, c) {
        if (!__hasProp.call(colAttrs, j)) return true;
        c = colAttrs[j];
        tr = document.createElement("tr");
        if (parseInt(j) === 0 && rowAttrs.length !== 0) {
          th = document.createElement("th");
          th.setAttribute("colspan", rowAttrs.length);
          th.setAttribute("rowspan", colAttrs.length);
          tr.appendChild(th);
        }
        th = document.createElement("th");
        th.className = "pvtAxisLabel";
        th.innerHTML = c;
        tr.appendChild(th);
        $.each(colKeys, function (i, c) {
          if (!__hasProp.call(colKeys, i)) return true;
          if (currentColPage > 1 && i < (currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) return true;
          if (i - ((currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) > GD_PIVOT_COLUMN_LIMIT) {
            colMaxed = true;
            colCount = colKeys.length;
            return false;
          }
          colKey = colKeys[i];
          x = spanSize(colKeys, parseInt(i), parseInt(j), currentColPage, GD_PIVOT_COLUMN_LIMIT);
          if (x !== -1) {
            if (i  - ((currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) + x > GD_PIVOT_COLUMN_LIMIT) {
              x = GD_PIVOT_COLUMN_LIMIT - (i - ((currentColPage - 1) * 100)) + 1;
            }
            th = document.createElement("th");
            th.className = "pvtColLabel";
            th.innerHTML = colKey[j];
            th.setAttribute("colspan", x);
            if (parseInt(j) === colAttrs.length - 1 && rowAttrs.length !== 0) {
              th.setAttribute("rowspan", 2);
            }
            tr.appendChild(th);
          }
        });
        if (parseInt(j) === 0) {
          th = document.createElement("th");
          th.className = "pvtTotalLabel";
          th.innerHTML = opts.localeStrings.totals;
          th.setAttribute("rowspan", colAttrs.length + (rowAttrs.length === 0 ? 0 : 1));
          tr.appendChild(th);
        }
        result.appendChild(tr);
      });
      if (rowAttrs.length !== 0) {
        tr = document.createElement("tr");
        $.each(rowAttrs, function(i, r) {
          if (!__hasProp.call(rowAttrs, i)) return true;
          r = rowAttrs[i];
          th = document.createElement("th");
          th.className = "pvtAxisLabel";
          th.innerHTML = r;
          tr.appendChild(th);
        });
        th = document.createElement("th");
        if (colAttrs.length === 0) {
          th.className = "pvtTotalLabel";
          th.innerHTML = opts.localeStrings.totals;
        }
        tr.appendChild(th);
        result.appendChild(tr);
      }
      $.each(rowKeys, function(i, row) {
        if (!__hasProp.call(rowKeys, i)) return true;
        if (currentRowPage > 1 && i < (currentRowPage - 1) * GD_PIVOT_ROW_LIMIT) return true;
        if (i - ((currentRowPage - 1) * GD_PIVOT_ROW_LIMIT) > GD_PIVOT_ROW_LIMIT) {
          rowMaxed = true;
          rowCount = rowKeys.length;
          return false;
        }
        rowKey = rowKeys[i];
        tr = document.createElement("tr");
        $.each(rowKey, function(j, r) {
          if (!__hasProp.call(rowKey, j)) return true;
          txt = rowKey[j];
          x = spanSize(rowKeys, parseInt(i), parseInt(j), currentRowPage, GD_PIVOT_ROW_LIMIT);
          if (x !== -1) {
            if (i  - ((currentRowPage - 1) * 100) + x > GD_PIVOT_ROW_LIMIT) {
              x = GD_PIVOT_ROW_LIMIT - (i  - ((currentRowPage - 1) * 100)) + 1;
            }
            th = document.createElement("th");
            th.className = "pvtRowLabel";
            th.innerHTML = txt;
            th.setAttribute("rowspan", x);
            if (parseInt(j) === rowAttrs.length - 1 && colAttrs.length !== 0) {
              th.setAttribute("colspan", 2);
            }
            tr.appendChild(th);
          }
        });
        $.each(colKeys, function(j, c) {
          if (!__hasProp.call(colKeys, j)) return true;
          if (currentColPage > 1 && j < (currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) return true;
          if (j - ((currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) > GD_PIVOT_COLUMN_LIMIT) {
            colMaxed = true;
            colCount = colKeys.length;
            return false;
          }
          colKey = colKeys[j];
          var cKey = [], rKey = [];
          $.each(colKey, function(i, c) {
              cKey.push(c);
          });
          $.each(rowKeys[i], function(i, r) {
            rKey.push(r);
          });
          val = lookupCell(cKey.join(" "), rKey.join(" "));
          td = document.createElement("td");
          td.className = "pvtVal row" + i + " col" + j;
          td.innerHTML = val;
          td.setAttribute("data-value", val);
          tr.appendChild(td);
        });
        var key = [];
        $.each(rowKeys[i], function(i, rk) {
          key.push(rk);
        });
        val = lookupRow(key.join(" "));
        td = document.createElement("td");
        td.className = "pvtTotal rowTotal";
        td.innerHTML = val;
        td.setAttribute("data-value", val);
        td.setAttribute("data-for", "row" + i);
        tr.appendChild(td);
        result.appendChild(tr);
      });
      tr = document.createElement("tr");
      th = document.createElement("th");
      th.className = "pvtTotalLabel";
      th.innerHTML = opts.localeStrings.totals;
      th.setAttribute("colspan", rowAttrs.length + (colAttrs.length === 0 ? 0 : 1));
      tr.appendChild(th);
      $.each(colKeys, function(j, c) {
        if (!__hasProp.call(colKeys, j)) return true;
        if (currentColPage > 1 && j < (currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) return true;
        if (j - ((currentColPage - 1) * GD_PIVOT_COLUMN_LIMIT) > GD_PIVOT_COLUMN_LIMIT) {
          colMaxed = true;
          colCount = colKeys.length;
          return false;
        }
        colKey = colKeys[j];
        var key = [];
        $.each(colKeys[j], function(i, ck) {
          key.push(ck);
        });
        val = lookupColumn(key.join(" "));
        td = document.createElement("td");
        td.className = "pvtTotal colTotal";
        td.innerHTML = val;
        td.setAttribute("data-value", val);
        td.setAttribute("data-for", "col" + j);
        tr.appendChild(td);
      });
      val = lookupTotal();
      td = document.createElement("td");
      td.className = "pvtGrandTotal";
      td.innerHTML = val;
      td.setAttribute("data-value", val);
      tr.appendChild(td);
      result.appendChild(tr);
      result.setAttribute("data-numrows", rowKeys.length);
      result.setAttribute("data-numcols", colKeys.length);
      container.appendChild(result);
      return container;
    };

    /*
    Pivot Table core: create PivotData object and call Renderer on it
     */
    $.fn.pivot = function(input, opts) {
      var defaults, e, pivotData, result, x, overlay;
      defaults = {
        cols: [],
        rows: [],
        filter: function() {
          return true;
        },
        aggregator: aggregatorTemplates.sum()([opts['measure']]),
        aggregatorName: "Sum",
        derivedAttributes: {},
        renderer: pivotTableRenderer,
        rendererOptions: null,
        localeStrings: locales.en.localeStrings
      };
      opts = $.extend(defaults, opts);
      result = null;
      overlay = null;
      try {
        pivotData = new PivotData(input, opts);
        try {
          result = opts.renderer(pivotData, opts.rendererOptions);
          $(result).css('max-width', opts.rendererOptions['width'] + "px");
          $(result).css('max-height', opts.rendererOptions['height'] + "px");
          if (opts.rendererOptions['draft']) {
            overlay = $('<img class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>');
          }
        } catch (_error) {
          e = _error;
          if (typeof console !== "undefined" && console !== null) {
            console.error(e.stack);
          }
          result = $("<span>").html(opts.localeStrings.renderError);
        }
      } catch (_error) {
        e = _error;
        if (typeof console !== "undefined" && console !== null) {
          console.error(e.stack);
        }
        result = $("<span>").html(opts.localeStrings.computeError);
      }
      x = this[0];
      while (x.hasChildNodes()) {
        x.removeChild(x.lastChild);
      }
      var ret = this.append(result);
      if (overlay) {
        if ($(result).width() < $(result).height()) {
          overlay.css('width', $(result).width() + "px");
          $(result).append(overlay);
          var diff = $(result).height() - $(result).width();
          var num = Math.ceil(diff/$(result).width());
          for (var i = 1; i <= num; i++) {
            $(result).append($('<img class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>').css('width', $(result).width() + "px").css("top", ($(result).width()*i) + "px"));
          }
        } else {
          overlay.css('height', $(result).height() + "px");
          $(result).append(overlay);
          var diff = $(result).width() - $(result).height();
          var num = Math.ceil(diff/$(result).height());
          for (var i = 1; i <= num; i++) {
              $(result).append($('<img class="report-draft-overlay" src="/sites/all/modules/custom/report/includes/images/draft.png"/>').css('height', $(result).height() + "px").css("left", ($(result).height()*i) + "px"));
          }
        }
      }
      return ret;
    };

    /*
    Pivot Table UI: calls Pivot Table core above with options set by user
     */
    $.fn.pivotUI = function(input, inputOpts, overwrite, locale) {
      var a, aggregator, attrLength, axisValues, c, colList, defaults, e, existingOpts, i, initialRender, k, opts, pivotTable, refresh, refreshDelayed, shownAttributes, tblCols, tr1, tr2, uiTable, unusedAttrsVerticalAutoOverride, x, _fn, _i, _j, _k, _l, _len, _len1, _len2, _len3, _ref, _ref1, _ref2, _ref3, _ref4;
      if (overwrite == null) {
        overwrite = false;
      }
      if (locale == null) {
        locale = "en";
      }
      defaults = {
        derivedAttributes: {},
        aggregators: locales[locale].aggregators,
        renderers: locales[locale].renderers,
        hiddenAttributes: [],
        menuLimit: 200,
        cols: [],
        rows: [],
        vals: [],
        exclusions: {},
        unusedAttrsVertical: "auto",
        autoSortUnusedAttrs: false,
        rendererOptions: {
          localeStrings: locales[locale].localeStrings
        },
        onRefresh: null,
        filter: function() {
          return true;
        },
        localeStrings: locales[locale].localeStrings
      };
      existingOpts = this.data("pivotUIOptions");
      if ((existingOpts == null) || overwrite) {
        opts = $.extend(defaults, inputOpts);
      } else {
        opts = existingOpts;
      }
      try {
        input = PivotData.convertToArray(input);
        if (opts['columns']) {
          tblCols = opts['columns'];
        } else {
          tblCols = {};
        }
        axisValues = {};
        for (_i in tblCols) {
          x = tblCols[_i];
          axisValues[x] = {};
        }
        uiTable = $("<table cellpadding='5'></table>");
        colList = $("<td class='pvtAxisContainer pvtUnused'>");

        shownAttributes = (function() {
          var _j, _len1, _results;
          _results = {};
          for (_j in tblCols) {
            c = tblCols[_j];
            if (__indexOf.call(opts.hiddenAttributes, c) < 0) {
              _results[_j] = c;
            }
          }
          return _results;
        })();

        unusedAttrsVerticalAutoOverride = false;
        if (opts.unusedAttrsVertical === "auto") {
          attrLength = 0;
          for (_j = 0, _len1 = shownAttributes.length; _j < _len1; _j++) {
            a = shownAttributes[_j];
            attrLength += a.length;
          }
          unusedAttrsVerticalAutoOverride = attrLength > 120;
        }
        if (opts.unusedAttrsVertical === true || !unusedAttrsVerticalAutoOverride) {
          colList.addClass('pvtVertList');
        } else {
          colList.addClass('pvtHorizList');
        }
        _fn = function(id, c) {
          var attrElem, btns, checkContainer, filterItem, filterItemExcluded, hasExcludedItem, keys, showFilterList, triangleLink, updateFilter, v, valueList, _k, _len2, _ref2;
          keys = (function() {
            var _results;
            _results = [];
            for (k in axisValues[c]) {
              _results.push(k);
            }
            return _results;
          })();

          updateFilter = function() {
            var unselectedCount;
            unselectedCount = $(valueList).find("[type='checkbox']").length - $(valueList).find("[type='checkbox']:checked").length;
            if (unselectedCount > 0) {
              attrElem.addClass("pvtFilteredAttribute");
            } else {
              attrElem.removeClass("pvtFilteredAttribute");
            }
            if (keys.length > opts.menuLimit) {
              return valueList.toggle();
            } else {
              return valueList.toggle(0, refresh);
            }
          };
          $("<p>").appendTo(valueList).append($("<button>").text("OK").bind("click", updateFilter));
          showFilterList = function(e) {
            valueList.css({
              left: e.pageX,
              top: e.pageY
            }).toggle();
            $('.pvtSearch').val('');
            return $('label').show();
          };
          triangleLink = $("<span class='pvtTriangle'>").html(" &#x25BE;").bind("click", showFilterList);
          //  TODO Disabled Filtering
          attrElem = $("<li class='axis_" + i + "'>").append($("<span class='pvtAttr'>").text(c).data("attrName", c).attr("id", id));//.append(triangleLink));
          if (hasExcludedItem) {
            attrElem.addClass('pvtFilteredAttribute');
          }
          colList.append(attrElem).append(valueList);
          return attrElem.bind("dblclick", showFilterList);
        };

        for (i in shownAttributes) {
          c = shownAttributes[i];
          _fn(i, c);
        }

        tr1 = $("<tr>").appendTo(uiTable);
        aggregator = $("<select class='pvtAggregator'>");
        aggregator.change(function() {
          var rows = [], cols = [], aggregate;
          _this.find(".pvtRows li span.pvtAttr").each(function() {
            return rows.push($(this).attr("id"));
          });
          _this.find(".pvtCols li span.pvtAttr").each(function() {
            return cols.push($(this).attr("id"));
          });
          aggregate = $(this).val();
          pivotTable.css("opacity", .5);
          $(document).trigger('update.gd.pivot',
              {
                rows: rows,
                measure: aggregate,
                columns: cols,
                callback: function(newData) {
                  return refresh(newData);
                }
              }
          );
        });
        _ref2 = opts.aggregators;
        var aggr = {};
        for (x in _ref2) {
          if (!__hasProp.call(_ref2, x)) continue;
          aggregator.append($("<option>").val(x).html(_ref2[x]['name']));
          aggr[x] = _ref2['aggregate'];
        }
        opts.aggregators = aggr;
        $("<td class='pvtVals'>").appendTo(tr1).append(aggregator).append($("<br>"));
        $("<td class='pvtAxisContainer pvtHorizList pvtCols'>").appendTo(tr1);
        tr2 = $("<tr>").appendTo(uiTable);
        tr2.append($("<td valign='top' class='pvtAxisContainer pvtRows'>"));
        pivotTable = $("<td valign='top' class='pvtRendererArea'>").appendTo(tr2);
        if (opts.unusedAttrsVertical === true || unusedAttrsVerticalAutoOverride) {
          uiTable.find('tr:nth-child(2)').prepend(colList);
        } else {
          uiTable.prepend($("<tr>").append(colList));
        }
        this.html(uiTable);
        _ref3 = opts.cols;
        for (_k = 0, _len2 = _ref3.length; _k < _len2; _k++) {
          x = _ref3[_k];
          if (x) {
            this.find(".pvtCols").append(this.find(".axis_" + x.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&")));
          }
        }
        _ref4 = opts.rows;
        for (_l = 0, _len3 = _ref4.length; _l < _len3; _l++) {
          x = _ref4[_l];
          if (x) {
            this.find(".pvtRows").append(this.find(".axis_" + x.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, "\\$&")));
          }
        }
        if (opts.aggregatorName != null) {
          this.find(".pvtAggregator").val(opts.aggregatorName);
        }
        if (opts.rendererName != null) {
          this.find(".pvtRenderer").val(opts.rendererName);
        }
        initialRender = true;
        refreshDelayed = (function(_this) {
          return function(input) {
            function makePageOption(sel, num) {
              var option = $('<option></option>');
              option.text(num);
              option.attr('value', num);
              sel.append(option);
            }

            function createRowPaging() {
              if (rowMaxed || currentRowPage != 1) {
                function changeRowPage(page, refreshCol) {
                  currentRowPage = page;
                  pivotTable.css("opacity", .5);
                  pivotTable.pivot(input, subopts);
                  pivotTable.css("opacity", 1);
                  createRowPaging();
                  createColPaging();
                }

                var pages = Math.ceil(rowCount/100);
                var i;
                _this.find('.pvtRows .row-paging').remove();
                var paging = $('<div class="row-paging"></div>');
                var sel = $('<select></select>');
                sel.change(function() {
                  changeRowPage($(this).val());
                });
                for (i = 0; i < pages; i++) {
                  makePageOption(sel, i+1);
                }
                sel.val(currentRowPage);
                paging.append($('<span>Row Page: </span>'), sel);
                _this.find('.pvtRows').append(paging);
              } else {
                _this.find('.pvtRows .row-paging').remove();
              }
            }

            function createColPaging() {
              function changeColPage(page) {
                currentColPage = page;
                pivotTable.css("opacity", .5);
                pivotTable.pivot(input, subopts);
                pivotTable.css("opacity", 1);
                createRowPaging();
                createColPaging();
              }
              if (colMaxed || currentColPage != 1) {
                var pages = Math.ceil(colCount/100);
                var i;
                _this.find('.pvtCols .col-paging').remove();
                var paging = $('<div class="col-paging"></div>');
                var sel = $('<select></select>');
                sel.change(function() {
                  changeColPage($(this).val());
                });
                for (i = 0; i < pages; i++) {
                  makePageOption(sel, i+1);
                }
                sel.val(currentColPage);
                paging.append($('<span>Column Page: </span>'), sel);
                var maxHeight = _this.find('.pvtCols').css('max-height');
                paging.css('bottom', (parseInt(maxHeight.substr(0, maxHeight.length - 2)) + 11) + "px");
                _this.find('.pvtCols').append(paging);
              } else {
                _this.find('.pvtCols .col-paging').remove();
              }
            }

            currentRowPage = 1;
            currentColPage = 1;
            var subopts;
            subopts = {
              localeStrings: opts.localeStrings,
              rendererOptions: opts.rendererOptions,
              cols: [],
              rows: []
            };
            _this.find(".pvtRows li span.pvtAttr").each(function() {
              return subopts.rows.push($(this).data("attrName"));
            });
            _this.find(".pvtCols li span.pvtAttr").each(function() {
              return subopts.cols.push($(this).data("attrName"));
            });
            subopts['measure'] = _this.find(".pvtAggregator").val();
            if (initialRender) {
              initialRender = false;
            }
            subopts.renderer = opts.renderers["Table"];
            pivotTable.pivot(input, subopts);
            pivotTable.css("opacity", 1);
            createRowPaging();
            createColPaging();
          };
        })(this);
        refresh = (function(_this) {
          return function(input) {
            pivotTable.css("opacity", 0.5);
            return setTimeout(function() {
              refreshDelayed(input);
            }, 10);
          };
        })(this);
        var _this = this;
        this.find(".pvtAxisContainer").sortable({
          update: function(e, ui) {
            if (ui.sender == null) {
              var rows = [], cols = [], aggregate;
              _this.find(".pvtRows li span.pvtAttr").each(function() {
                return rows.push($(this).attr("id"));
              });
              _this.find(".pvtCols li span.pvtAttr").each(function() {
                return cols.push($(this).attr("id"));
              });
              aggregate = _this.find(".pvtAggregator").val();
              pivotTable.css("opacity", .5);
              $(document).trigger('update.gd.pivot',
                {
                  rows: rows,
                  measure: aggregate,
                  columns: cols,
                  callback: function(newData) {
                    return refresh(newData);
                  }
                }
              );
            }
          },
          connectWith: this.find(".pvtAxisContainer"),
          items: 'li',
          placeholder: 'pvtPlaceholder'
        });
      } catch (_error) {
        e = _error;
        if (typeof console !== "undefined" && console !== null) {
          console.error(e.stack);
        }
        this.html(opts.localeStrings.uiRenderError);
      }

      pivotTable.css("opacity", .5);
      var _t = this;
      $(document).trigger('update.gd.pivot',
          {
            rows: opts.rows,
            measure: _t.find(".pvtAggregator").val(),
            columns: opts.cols,
            callback: function(newData) {
              return refresh(newData);
            }
          }
      );
      return this;
    };
  });

}).call(this);