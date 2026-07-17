<div class="card mb-3" id="googleSheetImport">
    <div class="card-header">
        <strong>Nhập từ Google Sheet</strong>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-auto col-form-label">
                <strong>Sheet ID:</strong>
            </div>
            <div class="col">
                <input type="text" class="form-control mb-2" name="sheet_id">
            </div>
        </div>
        <div class="row">
            <div class="col-auto col-form-label">
                <strong>Phạm vi :</strong>
            </div>
            <div class="col">
                <input type="text" class="form-control mb-2" name="range" placeholder="SheetName!A1:B2">
            </div>
        </div>
    </div>
    <div class="card-footer text-right">
        <button type="button" class="btn btn-outline-danger btn-delete">Xóa</button>
        <button type="button" class="btn btn-primary btn-import">Nhập dữ liệu</button>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        let container = $('#googleSheetImport');
        let btn_import = container.find('.btn-import');
        let btn_delete = container.find('.btn-delete');
        
        const local_storage_name = function() {
            let route_name = "{{ request()->route()->getName() }}";
            let camel = route_name.replace(/[-_ .]+(.)/g, (match, group1) => group1.toUpperCase()).replace(/^./, match => match.toLowerCase());
    
            return camel + 'GoogleInputs'
        }

        const saved_google_inputs = localStorage.getItem(local_storage_name);

        if (saved_google_inputs) {
            const input_values = JSON.parse(saved_google_inputs);
            $.each(input_values, (name, value) => container.find(`[name="${name}"]`).val(value));
        }

        container.find('input').on('input', function(e) {
            const input_values = {};
            container.find('input').each(function() {input_values[$(this).attr('name')] = $(this).val()});
            console.log(input_values);
            localStorage.setItem(local_storage_name, JSON.stringify(input_values));
        })

        btn_import.on('click', function(e) {
            let sheet_id = container.find('[name="sheet_id"]').val();
            let range = container.find('[name="range"]').val();

            if (sheet_id == '') {
                noty('Vui lòng nhập Sheet ID', 'error');
                return;
            }
            
            if (range == '') {
                noty('Vui lòng nhập phạm vi', 'error');
                return;
            }

            btn_import.prop('disabled', true);

            $.ajax({
                type: 'POST',
                url: "{{ route('google-sheet.read.post') }}",
                data: {
                    'sheet_id': sheet_id,
                    'range': range,
                },
            }).done(function(data) {
                $('textarea[name="list"]').val(data);
                noty('Nhập dữ liệu thành công', 'success');
            }).fail(function(xhr, status, error) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, (key, value) => noty(value[0], 'error'));
                } else {
                    console.log(xhr);
                    noty(xhr?.responseJSON?.message ?? 'Lỗi ngoại biên!', 'error');
                }
            }).always(function() {
                btn_import.prop('disabled', false);
            });
        });

        btn_delete.on('click', function(e) {
            container.find('input').val("");
            localStorage.setItem(local_storage_name, "");
        });
    });
</script>
@endpush