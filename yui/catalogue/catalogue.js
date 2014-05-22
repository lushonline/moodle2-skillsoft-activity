YUI.add('moodle-mod_skillsoft-catalogue', function(Y) {
    var CatalogueNAME = 'catalogue';
    var CATALOGUE = function() {
        CATALOGUE.superclass.constructor.apply(this, arguments);
    };
    Y.extend(CATALOGUE, Y.Base, {
        dragDelegate: null,
        skillsoftAssetInfoPanel: null,
        classificationPickerPanel: null,
        classificationFor: null,
        initializer : function(config) {
            Y.delegate('click', this.nodeClicked, '#skillsoft-catalogue', 'li.skillsoft-group a', this);
            Y.delegate('click', this.nodeClicked, '#skillsoft-catalogue', 'li.category a', this);
            Y.delegate('click', this.expandAll, '#skillsoft-catalogue', 'a.expand-all', this);
            Y.delegate('click', this.pickClassification, '#skillsoft-catalogue', 'ul.classification a', this);
            Y.all('a.classify-defaults').on('click', this.pickDefaultClassification, this);
            Y.delegate('click', this.pickClassification, '#skillsoft-catalogue', 'img.settings', this);
            Y.delegate('click', this.deleteNode, '#skillsoft-catalogue', 'img.delete', this);
            Y.delegate('click', this.showAssetInfo, '#skillsoft-catalogue', 'img.info', this);
            this.dragDelegate = new Y.DD.Delegate({
                container: '#skillsoft-catalogue',
                nodes: 'li.skillsoft-asset:not(.imported)'
            });
            this.dragDelegate.dd.plug(Y.Plugin.DDProxy, {
                moveOnEnd: false,
                cloneNode: true,
            });
            Y.all('#skillsoft-selected li').each(function(node) {
                var dropTarget = new Y.DD.Drop({
                    node: node,
                });
                dropTarget.on('drop:hit', this.dropHit, this);
                dropTarget.on('drop:enter', this.dropEnter, this);
                dropTarget.on('drop:exit', this.dropExit, this);
            }, this);
            this.skillsoftAssetInfoPanel = new Y.Panel({
                contentBox: Y.Node.create('<div id="skillsoft-asset-info-panel" />'),
                headerContent: 'Asset Info',
                bodyContent: '<div class="message" />',
                width: 600,
                height: 400,
                zIndex: 10,
                centered: true,
                modal: true,
                render: true,
                visible: false,
            });
            Y.one('#skillsoft-asset-info-panel div.yui3-widget-bd').setStyle('overflow', 'auto');

            this.initClassifyPanel(config.classify);
        },
        // Return the currently selected classifications as a comma separated string.
        getDialogClassifications: function() {
            var value = null;

            Y.all('#classify-form input[type=checkbox]:checked').each(function(checkbox) {
                if (value) {
                    value += ',';
                } else {
                    value = '';
                }
                value += checkbox.getAttribute('name');
            });
            if (value == null) {
                value = '';
            }
            return value;
        },
        // Set the currently selected classifications from a comma separated string.
        setDialogClassifications: function(values) {
            values = values.split(',');
            var pickerPanelNode = Y.one('#'+this.classificationPickerPanel.get('id'));
            pickerPanelNode.all('input[type=checkbox]').set('checked', false);
            for (var i in values) {
                var name = values[i];
                pickerPanelNode.all('input[name="'+name+'"]').set('checked', 'checked');
            }
        },
        renderClassifications: function(values, into) {
            into.all('> li').remove(true);
            var data = {};
            if (values != '') {
                values = values.split(',');
                for (var i in values) {
                    var value = values[i];
                    var id = Y.one('input[name="'+value+'"]').get('id');
                    var tab = Y.one('input[name="'+value+'"]').ancestor('div.yui3-tab-panel');
                    var caption = Y.one('#'+tab.get('aria-labelledby'));
                    var label = Y.one('#classify-form label[for='+id+']');
                    if (data[caption.getHTML()] === undefined) {
                        data[caption.getHTML()] = [];
                    }
                    data[caption.getHTML()].push(label.getHTML());
                }
            }
            for (var classification in data) {
                values = '';
                for (var i = 0; i < data[classification].length; i++) {
                    if (values != '') {
                        values += ',';
                    }
                    values += data[classification][i];
                }
                into.append('<li>'+classification+': '+values+'</li>');
            }
        },
        onClassificationChanged: function(context) {
            var values = context.getDialogClassifications();

            context.classificationFor.one('input.classify-list').setAttribute('value', values);
            context.renderClassifications(values, context.classificationFor.one('ul.classification'));
        },
        onDefaultClassificationChanged: function(context) {
            context.backupClassificationDefaults();
        },
        nodeClicked: function(e) {
            e.preventDefault();

            var parent = e.target.ancestor('li');
            if (parent.hasClass('skillsoft-group') || parent.hasClass('category')) {
                if (parent.hasClass('expanded')) {
                    this.collapseNode(parent);
                } else {
                    this.expandNode(parent, 0);
                }
            }
        },
        expandAll: function(e) {
            e.preventDefault();
            var header = e.target.ancestor('th');
            var clazz = this;

            if (header.hasClass('catalogue')) {
                Y.all('#skillsoft-catalogue td.catalogue li.collapsed').each(function(node) {
                    clazz.expandNode(node, 1);
                });
            }
            if (header.hasClass('categories')) {
                Y.all('#skillsoft-catalogue td.categories li.collapsed').each(function(node) {
                    clazz.expandNode(node, 1);
                });
            }
        },
        showAssetInfo: function(e) {
            var parent = e.target.ancestor('li');
            var href = 'ajax/asset.php?asset='+parent.getAttribute('data-asset');
            var cfg = {
                sync: true
            };
            var request = Y.io(href, cfg);
            Y.one('#skillsoft-asset-info-panel .message').setHTML(request.responseText);
            this.skillsoftAssetInfoPanel.show();
        },
        expandNode: function(node, recurse) {
            if (!node.one('ul').hasClass('loaded')) {
                var href = node.one('a').getAttribute('href')+'&recurse='+recurse;
                var cfg = {
                    sync: true
                };
                var request = Y.io(href, cfg);
                node.one('ul').append(request.responseText); // This works
                node.one('ul').addClass('loaded'); // As does this
                if (node.ancestor('td.categories')) {
                    var clazz = this;
                    node.all('ul li').each(function(childNode) {
                        var dropTarget = new Y.DD.Drop({
                            node: childNode
                        });
                        dropTarget.on('drop:hit', clazz.dropHit, clazz);
                        dropTarget.on('drop:enter', clazz.dropEnter, clazz);
                        dropTarget.on('drop:exit', clazz.dropExit, clazz);
                    });
                }
            };
            node.removeClass('collapsed');
            node.addClass('expanded');
        },
        collapseNode: function(node) {
            node.removeClass('expanded');
            node.addClass('collapsed');
        },
        deleteNode: function(e) {
            e.preventDefault();
            e.target.ancestor('li').remove(true);
        },
        dropHit: function(e) {
            e.preventDefault();
            var source = e.drag.get('node');
            var target = e.drop.get('node');
            target.removeClass('drop-hover');
            if (target.hasClass('collapsed')) {
                this.expandNode(target, 0);
            }
            var childNode;
            if (source.ancestor('li').hasClass('skillsoft-group')) {
                // This is an add
                childNode = e.drag.get('node').cloneNode(true);
                var asset = childNode.getAttribute('data-asset');
                childNode.prepend('<input type="hidden" name="asset_classification['+asset+']" class="classify-list" />');
                childNode.prepend('<input type="hidden" name="asset_category['+asset+']" class="category" />');
                childNode.prepend('<img src="'+M.util.image_url('i/settings')+'" class="settings">');
                childNode.prepend('<img src="'+M.util.image_url('t/delete')+'" class="delete">');
                childNode.append('<ul class="classification"></ul>');
                childNode.one('input.classify-list').setAttribute('value', this.defaultClassificationSelections);
                this.renderClassifications(this.defaultClassificationSelections, childNode.one('ul.classification'));
            } else {
                // This is a move
                childNode = source;
            }
            childNode.one('input.category').setAttribute('value', target.getAttribute('data-category'));
            target.one('ul').appendChild(childNode);
        },
        dropEnter: function(e) {
            var target = e.drop.get('node');
            if (target.ancestor('td').hasClass('categories')) {
                e.preventDefault();
                target.ancestor('td').all('.drop-hover').removeClass('drop-hover');
                target.addClass('drop-hover');
            }
        },
        dropExit: function(e) {
            var target = e.drop.get('node');
            if (target.ancestor('td').hasClass('categories')) {
                e.preventDefault();
                target.ancestor('td').all('.drop-hover').removeClass('drop-hover');
                if (target.ancestor('td.categories li')) {
                    target.ancestor('td.categories li').addClass('drop-hover');
                }
            }
        },
        pickClassification: function(e) {
            e.preventDefault();
            this.classificationFor = e.target.ancestor('li.skillsoft-asset');
            this.classificationPickerPanel.callback = this.onClassificationChanged;
            this.classificationPickerPanel.userdata = this;
            this.setDialogClassifications(this.classificationFor.one('input.classify-list').getAttribute('value'));
            this.classificationPickerPanel.show();
        },
        pickDefaultClassification: function(e) {
            e.preventDefault();
            this.classificationPickerPanel.callback = this.onDefaultClassificationChanged;
            this.classificationPickerPanel.userdata = this;
            this.setDialogClassifications(this.defaultClassificationSelections);
            this.classificationPickerPanel.show();
        },
        initClassifyPanel: function(node) {
            // First re-write the node to look good to Y.TabView
            var src = Y.one(node);
            var result = Y.Node.create('<div class="tabbed-panel"></div>');
            var ul = result.appendChild('<ul></ul>');
            var div = result.appendChild('<div></div>');

            src.all('fieldset').each(function () {
                var caption = this.one('legend').getHTML();
                var id = this.getAttribute('id');
                var content = this.one('div.fcontainer').getHTML();

                ul.appendChild('<li><a href="#'+id+'">'+caption+'</a></li>');
                div.appendChild('<div id="'+id+'">'+content+'</div>');
            });
            src.setHTML(result.getHTML());

            // Next convert the node to tabs
            var tabview = new Y.TabView({srcNode: src});
            tabview.render();

            var wrapper = Y.one('#'+tabview.get('id')).wrap('<div>');

            // Now wrap it all in a panel
            this.classificationPickerPanel = new Y.Panel({
                headerContent: 'Classify',
                srcNode: wrapper,
                width: 600,
                height: 400,
                zIndex: 10,
                centered: true,
                modal: true,
                render: true,
                visible: false,
                buttons : {
                    footer: [
                        {
                            name  : 'cancel',
                            label : 'Cancel',
                            action: 'onCancel'
                        },
                        {
                            name     : 'proceed',
                            label    : 'OK',
                            action   : 'onOK'
                        }
                    ]
                }
            });

            this.classificationPickerPanel.onOK = function(e) {
                e.preventDefault();
                this.callback(this.userdata);
                this.hide();
            };
            this.classificationPickerPanel.onCancel = function(e) {
                e.preventDefault();
                this.hide();
            };
            this.backupClassificationDefaults();
        },
        backupClassificationDefaults: function() {
            this.defaultClassificationSelections = this.getDialogClassifications();
        },
    }, {
        NAME : CatalogueNAME,
        ATTRS: {
        }
    });
    M.mod_skillsoft = M.mod_skillsoft || {};
    M.mod_skillsoft.init_catalogue = function(config) {
        return new CATALOGUE(config);
    };
}, '@VERSION@', {
    requires:['base', 'event', 'io', 'dd', 'panel', 'tabview']
});
