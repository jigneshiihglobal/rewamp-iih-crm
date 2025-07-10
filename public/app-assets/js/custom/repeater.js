function repeaterInit($form, config = {}) {
    let $tpl = $form.find('template');
    let $list = $form.find('[data-custom-repeater-list]');
    let listName = $list.attr('data-custom-repeater-list');
    let $items = $form.find('[data-custom-repeater-item]')
    let hasItems = $form.find('[data-custom-repeater-item]').length ? true : false;
    let itemCount = 0
    let maxIndex = 0;

    let initialData = (config && config.initialData && Array.isArray(config.initialData))
        ? config.initialData
        : null;

    let defaultData = (config && config.defaultData && typeof config.defaultData === 'object')
        ? config.defaultData
        : null;

    function create(defaults = {}) {
        let $newItem = $tpl.contents().filter(function () {
            return this.nodeType === Node.ELEMENT_NODE || (this.nodeType === Node.TEXT_NODE && $.trim(this.nodeValue) !== '');
        }).clone();
        $newItem.hide().appendTo($list)
        $newItem.attr('data-custom-repeater-list-index', maxIndex);
        if (itemCount > 0) {
            $form.find('[data-custom-repeater-delete]').show();
        } else {
            $form.find('[data-custom-repeater-delete]').hide();
        }

        $newItem.slideDown(function () {
        });
        itemCount++;
        maxIndex++;

        if (defaultData && typeof defaultData === 'object' && Object.keys(defaultData).length) {
            for (let [k, v] of Object.entries(defaultData)) {
                let $inp = $newItem.find(`[data-custom-repeater-name="${k}"]`)
                if ($inp) {
                    if ($inp.is('input')) {
                        $inp.val(v)
                    }

                    if ($inp.is('textarea')) {
                        $inp.val(v)
                    }

                    if ($inp.is('select')) {
                        if ($inp.prop('multiple')) {
                            if ($inp.hasClass('select2')) {
                                $inp.empty();
                                if (Array.isArray(v)) {
                                    v.forEach(function (val, i) {
                                        $inp.append(new Option(val, val, true, true));
                                    })
                                } else {
                                    $inp.append(new Option(v, v, true, true));
                                }
                            } else {
                                $inp.val(v)
                            }
                        } else {
                            $inp.val(v)
                        }
                    }

                    if ($inp.is(':checkbox')) {
                    }

                    if ($inp.is(':radio')) {
                    }
                }
            }
        }


        if (defaults && typeof defaults === 'object' && Object.keys(defaults).length) {
            for (let [k, v] of Object.entries(defaults)) {
                let $inp = $newItem.find(`[data-custom-repeater-name="${k}"]`)
                if ($inp) {
                    if ($inp.is('input')) {
                        $inp.val(v)
                    }

                    if ($inp.is('textarea')) {
                        $inp.val(v)
                    }

                    if ($inp.is('select')) {
                        if ($inp.hasClass('select2') && $inp.prop('multiple')) {
                            $inp.empty();
                            if (Array.isArray(v)) {
                                v.forEach(function (val, i) {
                                    $inp.append(new Option(val, val, true, true));
                                })
                            } else {
                                $inp.append(new Option(v, v, true, true));
                            }
                        } else {
                            $inp.val(v)
                        }
                    }

                    if ($inp.is(':checkbox')) {
                    }

                    if ($inp.is(':radio')) {
                    }
                }
            }
        }

        renameInputs();

        $newItem.on(
            'click',
            "[data-custom-repeater-delete]",
            removeBtnClickHandler
        );
        callAfterCreated($newItem);
    }

    function callAfterCreated($item) {
        if (config && config.afterCreated && typeof config.afterCreated === 'function') {
            config.afterCreated($item)
        }
    }

    function callBeforeDestroyed($item, cb = function () { }) {
        if (config && config.beforeDestroyed && typeof config.beforeDestroyed === 'function') {
            if (cb && typeof cb === 'function') {
                return config.beforeDestroyed($item, cb)
            } else {
                return config.beforeDestroyed($item)
            }
        }
    }

    function renameInputs() {
        $form.find('[data-custom-repeater-item]').each(function (index) {
            let $item = $(this)
            let itemIndex = $item.data('custom-repeater-list-index')
            $item.find('[data-custom-repeater-name]').each(function (inputIndex) {
                let $anyInput = $(this);
                let name = $anyInput.attr('data-custom-repeater-name');
                let brackets = $anyInput.prop('multiple') ? "[]" : "";
                $anyInput.attr('name', `${listName}[${itemIndex}][${name}]${brackets}`)
            })
        })
    }

    function removeBtnClickHandler(e) {
        let $item = $(e.target).closest('[data-custom-repeater-item]');
        if (!callBeforeDestroyed($item, function (doContinue) {
            if (doContinue) {
                $item.off(
                    'click',
                    "[data-custom-repeater-delete]",
                    removeBtnClickHandler
                );

                $item.slideUp(function () {
                    if (itemCount === 2) $form.find('[data-custom-repeater-delete]').hide();
                    $(this).remove();
                    itemCount--
                });
            }
        })) return;
    }

    $form.on('click', "[data-custom-repeater-create]", (event) => create())

    let doCreate = true;

    if (config?.initEmpty || initialData?.length) {
        doCreate = false;
    }

    if (initialData?.length) {
        initialData.forEach(item => {
            create(item);
        });
    }

    if (doCreate) {
        create();
    }
}
