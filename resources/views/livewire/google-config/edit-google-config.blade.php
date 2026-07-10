<div wire:ignore.self id="editGoogleConfig" class="modal fade" tabindex="-1" aria-modal="true" role="dialog" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form wire:submit.prevent="save">
                <div class="modal-header">
                    <h5 class="modal-title">Cấu hình Google</h5>
                </div>
    
                <div class="modal-body">
                    <div class="row mb-2">
                        <div class="col">
                            <strong>Client ID</strong>
                            <input type="text" class="form-control" wire:model.lazy="params.client_id">
                            @error('params.client_id') 
                            <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <strong>Client secret</strong>
                            <input type="text" class="form-control" wire:model.lazy="params.client_secret">
                            @error('params.client_secret') 
                            <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <strong>Redirect URI</strong>
                            <input type="text" class="form-control" wire:model.lazy="params.redirect_uri">
                            @error('params.redirect_uri') 
                            <span class="text-danger">{{ $message }}</span> 
                            @enderror
                        </div>
                    </div>
                </div>
    
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                        <i class="fa-solid fa-floppy-disk mr-1"></i>
                        Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>