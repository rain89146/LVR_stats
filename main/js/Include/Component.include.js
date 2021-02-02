/**
 * Templates of each component
 */
export default function Component() {

    /**
     * Grid card holder
     * @param {*} props 
     */
    function grid_card_holder(props) {
        let { title, tab_id, filter_id, content_id, container } = props;
        let holder = `
        <div class="sold_listing_city_con">
            <div class="flex flex-column gap-sm flex-row@sm justify-between@sm items-baseline@sm">
                <h1 class="text-lg">${title}</h1>
            </div>
            <div class="margin-bottom-sm">
                <div id="${tab_id}"></div>
            </div>
            <div class="margin-bottom-md">
                <div class="flex" id="${filter_id}"></div>
            </div>
            <div class="grid gap-sm" style="margin-bottom: 0;" id="${content_id}"></div>
        </div>
        `;
        $("#" + container).html(holder);
    }

    /**
     * Grid card template
     * @param {*} props 
     */
    function grid_card_template(props) {
        let { title, remark, value, growth, url, container } = props;

        //  set the icon color based on the growth number
        let icon_color = (growth.indexOf('-') !== -1) ? `color-error` : `color-success`;

        //  set the icon based on the growth number
        let grow_icon = (growth.indexOf('-') !== -1)
            ? `
            <svg class="icon icon--sm flip-y margin-right-xxxxs" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="12" fill="currentColor" opacity="0.2"></circle>
                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                    <polyline points="7 11 12 6 17 11"></polyline>
                    <line x1="12" y1="18" x2="12" y2="6"></line>
                </g>
            </svg>
            `
            : `
            <svg class="icon icon--sm margin-right-xxxxs" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="12" fill="currentColor" opacity="0.2"></circle>
                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                <polyline points="7 11 12 6 17 11"></polyline>
                <line x1="12" y1="18" x2="12" y2="6"></line>
                </g>
            </svg>
            `

        let holder = `
        <a class="link-card flex flex-column bg radius-md col-6@sm col-3@xl" href="${url}" aria-label="Link label">
            <div class="padding-md">
                <div class="stats-card bg">
                <p class="color-contrast-high font-semibold">${title}</p>
                <div class="margin-top-md text-xs color-contrast-high">${remark}</div>
                    <p class="margin-bottom-sm text-xxl font-semibold color-contrast-higher">${value}</p>
                    <span class="flex items-center ${icon_color} margin-top-xxs">
                        ${grow_icon}
                        <i>${growth}</i>
                    </span>
                </div>
            </div>
            <div class="link-card__footer margin-top-auto border-top border-contrast-lower">
                <p class="text-sm">View</p>
                <div><svg class="icon icon--sm" viewBox="0 0 24 24"><g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><polyline points="15 6 21 12 15 18"></polyline></g></svg></div>
            </div>
        </a>
        `;

        $("#" + container).append(holder);
    }

    /**
     * Grid card without link template
     * @param {*} props 
     */
    function grid_card_without_link_template(props) {
        let { title, value, growth, container } = props;

        //  set the icon color based on the growth number
        let icon_color = (growth.indexOf('-') !== -1) ? `color-error` : `color-success`;

        //  set the icon based on the growth number
        let grow_icon = (growth.indexOf('-') !== -1)
            ? `
            <svg class="icon icon--sm flip-y margin-right-xxxxs" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="12" fill="currentColor" opacity="0.2"></circle>
                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                    <polyline points="7 11 12 6 17 11"></polyline>
                    <line x1="12" y1="18" x2="12" y2="6"></line>
                </g>
            </svg>
            `
            : `
            <svg class="icon icon--sm margin-right-xxxxs" viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="12" fill="currentColor" opacity="0.2"></circle>
                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
                <polyline points="7 11 12 6 17 11"></polyline>
                <line x1="12" y1="18" x2="12" y2="6"></line>
                </g>
            </svg>
            `

        let holder = `
        <div class="link-card flex flex-column bg radius-md col-6@xl col-3@xl">
            <div class="padding-md">
                <div class="stats-card bg">
                    <p class="color-contrast-medium margin-bottom-xxs">${title}</p>
                    <p class="margin-bottom-md text-xxl font-semibold color-contrast-higher">${value}</p>
                    <span class="flex items-center ${icon_color} margin-top-xxs">
                        ${grow_icon}
                        <i>${growth}</i>
                    </span>
                </div>
            </div>
        </div>
        `;

        $("#" + container).append(holder);
    }

    /**
     * Two by two chart grid card holder
     * @param {*} props 
     */
    function two_by_two_chart_grid_card_holder(props) {
        let { title, controller_id, content_id, tab_id, container } = props;
        let controller = (typeof controller_id !== 'undefined' && controller_id !== '') ? `<div class="flex" id="${controller_id}"></div>` : ``;
        let tab = (typeof tab_id !== 'undefined' && tab_id !== '') ? `<div class="margin-bottom-sm"><div id="${tab_id}"></div></div>` : '';
        title = (typeof title !== 'undefined' && title !== '') ? `<div class="margin-bottom-md"><h1 class="text-lg">${title}</h1></div>` : '';
        let holder = `
        <div>
            ${title}${tab}${controller}
            <div class="grid gap-sm" id="${content_id}"></div>
        </div>
        `;
        $("#" + container).html(holder);
    }

    /**
     * Grid card with chart
     * @param {*} props 
     */
    function grid_card_with_chart(props) {
        let { title, growth, value, chart_id, delay } = props;

        let grow_icon = (growth.indexOf('-') !== -1)
            ? `<span class="inline-block bg-error bg-opacity-20% text-xs padding-x-xxs padding-y-xxxxs radius-full">${growth}</span>`
            : `<span class="inline-block bg-success bg-opacity-20% text-xs padding-x-xxs padding-y-xxxxs radius-full">${growth}</span>`

        return `
        <div class="stats-card bg radius-md padding-md shadow-xs col-6@xl col-3@xl">
            <div class="flex flex-wrap gap-xxs items-center">
                <div><p class="color-contrast-high font-semibold">${title}</p></div>
                <div class="flex items-center">${grow_icon}</div>
            </div>
            <p class="text-xxl font-semibold color-contrast-higher margin-top-xs">${value}</p>
            <div class="margin-top-md">
                <div class="chart chart--area">
                    <canvas id="${chart_id}"></canvas>
                </div>
            </div>
        </div>
        `;
    }

    /**
     * Grid card with chart by four
     * @param {*} props 
     */
    function grid_card_with_chart_by_four(props) {
        let { title, growth, value, chart_id, delay } = props;

        let grow_icon = (growth.indexOf('-') !== -1)
            ? `<span class="inline-block bg-error bg-opacity-20% text-xs padding-x-xxs padding-y-xxxxs radius-full">${growth}</span>`
            : `<span class="inline-block bg-success bg-opacity-20% text-xs padding-x-xxs padding-y-xxxxs radius-full">${growth}</span>`

        return `
        <div class="stats-card bg radius-md padding-md shadow-xs col-6@sm col-3@xl">
            <div class="flex flex-wrap gap-xxs items-center">
                <div><p class="color-contrast-high font-semibold">${title}</p></div>
                <div class="flex items-center">${grow_icon}</div>
            </div>
            <p class="text-xxl font-semibold color-contrast-higher margin-top-xs">${value}</p>
            <div class="margin-top-md">
                <div class="chart chart--area">
                    <canvas id="${chart_id}"></canvas>
                </div>
            </div>
        </div>
        `;
    }

    /**
     * Grid card with chart by three
     * @param {*} props 
     */
    function grid_card_with_chart_by_three(props) {
        let { title, growth, value, chart_id, delay } = props;

        let grow_icon = (growth.indexOf('-') !== -1)
            ? `<span class="inline-block bg-error bg-opacity-20% text-xs padding-x-xxs padding-y-xxxxs radius-full">${growth}</span>`
            : `<span class="inline-block bg-success bg-opacity-20% text-xs padding-x-xxs padding-y-xxxxs radius-full">${growth}</span>`

        return `
        <div class="bg radius-md padding-md shadow-xs col-12 col-4@sm">
            <div class="flex flex-wrap gap-xxs items-center">
                <div><p class="color-contrast-high font-semibold">${title}</p></div>
                <div class="flex items-center">${grow_icon}</div>
            </div>
            <p class="text-xxl font-semibold color-contrast-higher margin-top-xs">${value}</p>
            <div class="margin-top-md">
                <div class="chart chart--area">
                    <canvas id="${chart_id}"></canvas>
                </div>
            </div>
        </div>
        `;
    }

    /**
     * Grid card with pie chart
     * @param {*} props 
     */
    function grid_card_with_pie_chart(props) {
        let { title, chart_id } = props;
        return `
        <div class="stats-card bg radius-md padding-md shadow-xs col-6@xl col-3@xl">
            <div class="flex flex-wrap gap-xxs items-center margin-bottom-sm">
                <div><p class="color-contrast-high font-semibold">${title}</p></div>
            </div>
            <div style="display: flex; justify-content: center; ">
                <div class="chart chart--area">
                    <canvas id="${chart_id}" style="position:relative"></canvas>
                </div>
            </div>
            <div class="margin-top-sm">
                <ul class="grid gap-xs" id="${chart_id}_legend"></ul>
            </div>
        </div>
        `;
    }

    /**
     * Grid card with pie chart
     * @param {*} props 
     */
    function grid_card_with_regular_chart(props) {
        let { title, chart_id } = props;
        return `
        <div class="stats-card bg radius-md padding-md shadow-xs col-6@xl col-3@xl" style="position: relative;">
            <div class="flex flex-wrap gap-xxs items-center margin-bottom-sm">
                <div><p class="color-contrast-high font-semibold">${title}</p></div>
            </div>
            <div style="height: 100%; position: absolute; top: 0; width: 100%; left: 0">
                <div class="chart chart--area padding-bottom-md padding-left-md padding-right-md" style="height: 100%; padding-top: 4rem;">
                    <canvas id="${chart_id}" style="position:relative;height: 100% !important"></canvas>
                </div>
            </div>
        </div>
        `;
    }

    /**
     * Basic table with sort feature
     * @param {*} props 
     */
    function basic_table_with_sort_feature(props) {

        let { title, head_id, table_id } = props;

        return `
        <div class="bg radius-md padding-md shadow-xs col-12">
            <p class="color-contrast-high margin-bottom-md font-semibold" id="${table_id}_title">${title}</p>
            <div class="tbl text-sm">
                <table class="tbl__table border-bottom" aria-label="${title}">
                    <thead class="tbl__header border-bottom">
                        <tr class="tbl__row" id="${head_id}"></tr>
                    </thead>
                    <tbody class="tbl__body" id="${table_id}"></tbody>
                </table>
            </div>
            <div id="${table_id}_pag"></div>
        </div>
        `;
    }

    /**
     * Table head coloumn
     * @param {*} props 
     */
    function table_head_coloumn(props) {

        //  The coloumns are array of data
        let { coloumns } = props;

        //  Construct the table head
        let head = '';
        coloumns.forEach(col => {

            //  which contains, name, id, and text
            let { coloumn_name, coloumn_id, text_pos, sortable } = col;

            //  set icons
            let icon = (sortable) ? `<svg class="icon icon--xxs margin-left-xxxs int-table__sort-icon" aria-hidden="true" viewBox="0 0 12 12"><polygon class="arrow-up" points="6 0 10 5 2 5 6 0"></polygon><polygon class="arrow-down" points="6 12 2 7 10 7 6 12"></polygon></svg>` : '';
            let class_sort = (sortable) ? ' sortable' : '';
            //  Append to the head
            head += `
                <th class="tbl__cell text-${text_pos} js-int-table__cell--sort${class_sort}" scope="col" id="${coloumn_id}">
                    <span class="font-semibold">${coloumn_name}</span>
                    ${icon}
                </th>
            `
        });

        return head;
    }

    /**
     * Table row
     * @param {*} props 
     */
    function table_row(rows_data) {

        //  The rows_data are array of data
        let elements = '';
        if (typeof rows_data !== 'undefined' && Array.isArray(rows_data) && rows_data.length !== 0) {
            rows_data.forEach((datas, index) => {

                let delay = 20 * index;

                let row = '';
                datas.forEach(col => {
                    if (typeof col != 'undefined' && col != '') {
                        row += `<td class="tbl__cell" role="cell">${col}</td>`;
                    }
                });

                elements += `<tr class="tbl__row" data-aos="fade-down" data-aos-delay="${delay}">${row}</tr>`;
            });
        }
        return elements;
    }

    //API
    this.table_row = table_row;
    this.table_head_coloumn = table_head_coloumn;
    this.basic_table_with_sort_feature = basic_table_with_sort_feature;

    this.grid_card_with_pie_chart = grid_card_with_pie_chart;
    this.grid_card_with_chart = grid_card_with_chart;
    this.grid_card_with_chart_by_four = grid_card_with_chart_by_four;
    this.grid_card_with_chart_by_three = grid_card_with_chart_by_three;
    this.two_by_two_chart_grid_card_holder = two_by_two_chart_grid_card_holder;
    this.grid_card_with_regular_chart = grid_card_with_regular_chart;

    this.grid_card_holder = grid_card_holder;
    this.grid_card_template = grid_card_template;
    this.grid_card_without_link_template = grid_card_without_link_template;
}