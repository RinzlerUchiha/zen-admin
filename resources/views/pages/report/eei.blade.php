<link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.bootstrap5.min.css">
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.2.2/js/dataTables.bootstrap5.min.js"></script>

<style>
    #input-group-eei-ym,
    #eei-month {
        max-width: fit-content;
    }

    #eei-year {
        max-width: 100px;
    }

    #new-eei-div * {
        font-size: 12px;
    }

    .top-20 {
        top: 20%;
    }
</style>
<div class="container-fluid">
    <div class="d-flex justify-content-end mb-3">
        <div class="input-group input-group-sm" id="input-group-eei-ym">
            <span class="input-group-text" id="eei-month-label">MONTH</span>
            <select class="form-select form-select-sm" id="eei-month">
                <option value="01">JANUARY</option>
                <option value="02">FEBRUARY</option>
                <option value="03">MARCH</option>
                <option value="04">APRIL</option>
                <option value="05">MAY</option>
                <option value="06">JUNE</option>
                <option value="07">JULY</option>
                <option value="08">AUGUST</option>
                <option value="09">SEPTEMBER</option>
                <option value="10">OCTOBER</option>
                <option value="11">NOVEMBER</option>
                <option value="12">DECEMBER</option>
            </select>
            <input type="number" class="form-control form-control-sm" id="eei-year" value="{{ date('Y') }}">
            <button class="btn btn-outline-secondary" type="button" id="btn-load-eei"><i class="fa fa-search"></i></button>
        </div>
    </div>
    <div id="new-eei-div"></div>
</div>

<script>
    $(function() {
        $('#btn-load-eei').click(function() {
            get_list();
        });

        let inputtime;
        $('#new-eei-div').on('input', '.dataTables_filter [type="search"]', function() {
            clearTimeout(inputtime);
            $('#new-eei-div #tbl-eei tbody tr td:first-child').attr('rowspan', 1);
            $('#new-eei-div #tbl-eei tbody tr td:first-child').show();
            inputtime = setTimeout(() => adjust_eei_tbl(), 700);
        });
    });

    async function get_list() {
        $('#new-eei-div').html("<h3 class='mx-auto my-auto'>Loading...</h3>");
        try {
            // Make the fetch request to the Laravel controller
            const response = await fetch('/report/eei/list/'+$('#eei-year').val()+'-'+$('#eei-month').val());
            
            if (!response.ok) { // Check if the response was successful
                throw new Error('Network response was not ok');
            }

            // Get the response text (HTML)
            const html = await response.text();

            // Inject the received HTML into the DOM
            $('#new-eei-div').html(html);
            $('#new-eei-div #tbl-eei').DataTable({
                'scrollY': '70vh',
                // 'scrollX': '100%',
                'scrollCollapse': true,
                'paging': false,
                'ordering': false,
                'info': false
            });
            adjust_eei_tbl();
        } catch (error) {
            console.error('Error fetching the list:', error);
        }
    }

    function adjust_eei_tbl() {
        // $('#new-eei-div #tbl-eei tbody tr td:first-child').show();
        let curtext = '';
        let spancnt = 1;
        let curelem;
        $('#new-eei-div #tbl-eei tbody tr').each(function() {
            if (curtext == $(this).find('td').eq(0).text()) {
                $(this).find('td').eq(0).hide();
                spancnt++;
            } else {
                if (curelem) {
                    curelem.attr('rowspan', spancnt);
                }
                curelem = $(this).find('td').eq(0);
                curtext = $(this).find('td').eq(0).text();
                spancnt = 1;
            }
        });
        if (curelem) {
            curelem.attr('rowspan', spancnt);
        }
    }
</script>