YUI.add('moodle-mod_skillsoft-catalogue', function(Y) {
    var CatalogueNAME = 'catalogue';
    var CATALOGUE = function() {
        CATALOGUE.superclass.constructor.apply(this, arguments);
    };
    Y.extend(CATALOGUE, Y.Base, {
        dragDelegate: null,
        skillsoftAssetInfoPanel: null,
        topicsPickerPanel: null,
        topicsFor: null,
        initializer : function(config) {
            Y.delegate('click', this.nodeClicked, '#skillsoft-catalogue', 'li.skillsoft-group a', this);
            Y.delegate('click', this.nodeClicked, '#skillsoft-catalogue', 'li.category a', this);
            Y.delegate('click', this.expandAll, '#skillsoft-catalogue', 'a.expand-all', this);
            Y.delegate('click', this.pickTopics, '#skillsoft-catalogue', 'ul.topics a', this);
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
                contentBox : Y.Node.create('<div id="skillsoft-asset-info-panel" />'),
                bodyContent : '<div class="message" />',
                width: 600,
                height: 400,
                zIndex: 10,
                centered: true,
                modal: true,
                render: true,
                visible: false,
            });
            Y.one('#skillsoft-asset-info-panel div.yui3-widget-bd').setStyle('overflow', 'auto');
            this.topicsPickerPanel = new Y.Panel({
                contentBox : Y.Node.create('<div id="topics-picker-panel" />'),
                bodyContent : '<div class="message">'+config.topics+'</div>',
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
            this.topicsPickerPanel.onOK = function(e) {
                e.preventDefault();
                this.callback(this.userdata);
                this.hide();
            };
            this.topicsPickerPanel.onCancel = function(e) {
                e.preventDefault();
                this.hide();
            };
            this.topicsPickerPanel.callback = this.onTopicsChanged;
            this.topicsPickerPanel.userdata = this;
            Y.one('#topics-picker-panel div.yui3-widget-bd').setStyle('overflow', 'auto');
        },
        onTopicsChanged: function(context) {
            var topicList = null;

            context.topicsFor.all('> li').remove(true);
            Y.all('#topics-picker-panel input[type=checkbox]').each(function(checkbox) {
                var match = checkbox.getAttribute('name').match(/tool_topics_topics\[(\d+)\]/);
                if (match && checkbox.get('checked')) {
                    var label = Y.one('#topics-picker-panel label[for='+checkbox.get('id')+']');
                    context.topicsFor.append('<li>'+label.getHTML()+'</li>');
                    if (topicList) {
                        topicList += ',';
                    } else {
                        topicList = '';
                    }
                    topicList += match[1];
                }
            });
            context.topicsFor.ancestor('li.skillsoft-asset').one('input.topic-list').setAttribute('value', topicList);
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
                childNode.prepend('<input type="hidden" name="asset_topics['+asset+']" class="topic-list" />');
                childNode.prepend('<input type="hidden" name="asset_category['+asset+']" class="category" />');
                childNode.prepend('<img src="'+M.util.image_url('t/delete')+'" class="delete">');
                childNode.append('<ul class="topics"><a href="?pick-topics=1">Choose topics</a></ul>');
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
        pickTopics: function(e) {
            e.preventDefault();
            this.topicsFor = e.target.ancestor('ul');
            this.topicsPickerPanel.show();
        }
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
    requires:['base', 'event', 'io', 'dd', 'panel']
});
