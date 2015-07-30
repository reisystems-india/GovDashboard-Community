(function(global,$,undefined) {

    if ( typeof $ === 'undefined' ) {
        throw new Error('Formula requires jQuery');
    }

    if ( typeof global.GD === 'undefined' ) {
        throw new Error('Formula requires GD');
    }

    var GD = global.GD;

    var Formula = GD.Class.extend({
        id: null,
        name: null,
        type: null,
        expression: null,
        expressionLanguage: null,
        version: null,

        init: function(object) {
            if (object) {
                this.setID(object['name']);
                this.setName(object['publicName']);
                this.setType(object['type']);
                this.setExpression(object['expression']);
                this.setExpressionLanguage(object['expressionLanguage']);
                this.setVersion(object['version']);
            }
        },

        setExpression: function(expression) {
            if (this.expression != expression) {
                //  Automatically generate a new version when expression is changed
                this.generateVersion();
                this.expression = expression;
            }
        },

        getExpression: function() {
            return this.expression;
        },

        setType: function(type) {
            this.type = type;
        },

        getType: function() {
            return this.type;
        },

        setExpressionLanguage: function(language) {
            this.expressionLanguage = language;
        },

        getExpressionLanguage: function() {
            return this.expressionLanguage;
        },

        setName: function(name) {
            this.name = name;
        },

        getName: function() {
            return this.name;
        },

        setID: function(id) {
            if (id) {
                this.id = id;
            }
        },

        generateID: function() {
            this.id = 'formula:' + GD.Utility.generateUUID();
        },

        getID: function() {
            return this.id;
        },

        setVersion: function(version) {
            if (version) {
                this.version = version;
            }
        },

        generateVersion: function() {
            this.version = GD.Utility.generateUUID();
        },

        getVersion: function() {
            return this.version;
        },

        getRaw: function() {
            if (!this.id) {
                this.generateID();
            }

            if (!this.version) {
                this.generateVersion();
            }

            return {
                name: this.id,
                publicName: this.name,
                type: this.type,
                expression: this.expression,
                expressionLanguage: this.expressionLanguage,
                version: this.version
            };
        }
    });

    // add to namespace
    GD.Formula = Formula;

    GD.Formula.clone = function(formula) {
        var f = new GD.Formula();
        f.id = formula.getID();
        f.name = formula.getName();
        f.type = formula.getType();
        f.expression = formula.getExpression();
        f.expressionLanguage = formula.getExpressionLanguage();
        f.version = formula.getVersion();
        return f;
    };

    GD.Formula.canBeUsedInCalculation = function (name) {
        //  TODO Remove third condition soon
        return name.indexOf(':') !== -1 && name.indexOf('measure:') !== 0 && name.indexOf('.') === -1;
    };

    GD.Formula.isFormula = function(name) {
        return name.indexOf('formula:') === 0;
    };

    GD.Formula.compareFormulas = function(a, b) {
        return a.getID() === b.getID();
    };

    GD.Formula.isMeasure = function (formula) {
        var isMeasure = false;

        var expression = formula['expression'];
        if (expression) {
            isMeasure = expression.toLowerCase().indexOf('count(') !== -1 || expression.toLowerCase().indexOf('sum(') !== -1 || expression.toLowerCase().indexOf('max(') !== -1 || expression.toLowerCase().indexOf('min(') !== -1 || expression.toLowerCase().indexOf('avg(') !== -1;
        }

        return isMeasure;
    }

})(typeof window === 'undefined' ? this : window, jQuery);