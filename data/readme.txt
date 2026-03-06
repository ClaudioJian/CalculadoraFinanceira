this file is to explain JSON format for developers

selector.JSON:
    - "class": can hold multiple class, separete with space to indentify. put "none" or "" if don't exist
    - "filterID": indentifier to tell other selector, hide some of content depedent of itself. if no other selector is depedent to this selector, put "none" or ""
    - "depencityID": find selector with same filterID and hide some itens depedent innerElement of this selector. if selector's depencityID isn't none, innerElement must be "none" or ""
    - "default_selected": element is displayed by default inside of selector
        - "type" : indentifier to make other selector understand what itens to hide. only needed if has filterID.
        - "depencitytype": find itens selected with same value as type, showing only matches. only needed if selector has depencityID.
        - "order": is what order when is put in items, must be different for each itens
        - "innerElement": define what is inside of item selected, follow with format below, if just text, put only string
            - "element_type": tell which type of element is inside, for example, div. if it don't contain nothing, put "none" or "", or if is just text, put "text"
            - "innerText"
            - "class"
            - "tags": specific what tag is inside this element, for example, src. if exist multiple element put separate element inside "{}" with all tag name and value
            - "innerElements": put inside inside new element with same format as innerElement, creating children inside of that element. if exist multiple element put "[]" and "," to separate element, elements in same list will not became children
                                for example:  "innerElements":[{
                                    "element_type":"img";
                                    "innerText":"none";
                                    "class":"img-size";
                                    "tags": {"src"="image/..."};
                                    innerElements:{...}},{...}
                                ]
    - "itens" : list contain all innerElements, following "default_selected" format.