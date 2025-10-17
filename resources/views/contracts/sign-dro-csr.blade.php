@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-900">Initial DRO Form as CSR</h2>
        </div>
        
        <div class="px-6 py-4">
            <p class="mb-4">Please provide your initials below to confirm you have reviewed the DRO form.</p>
            
            <div wire:ignore x-data="{
                signaturePad: null,
                initials: '',
                init() {
                    this.signaturePad = new SignaturePad(this.$refs.canvas, {
                        backgroundColor: 'rgb(249, 250, 251)',
                        penColor: 'rgb(0, 0, 0)'
                    });
                    this.resizeCanvas();
                },
                save() {
                    if (this.signaturePad.isEmpty()) {
                        alert('Please provide your initials.');
                        return;
                    }
                    this.initials = this.signaturePad.toDataURL('image/png');
                    document.getElementById('initialsInput').value = this.initials;
                    document.getElementById('initialsForm').submit();
                },
                clear() {
                    this.signaturePad.clear();
                },
                resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    this.$refs.canvas.width = this.$refs.canvas.offsetWidth * ratio;
                    this.$refs.canvas.height = this.$refs.canvas.offsetHeight * ratio;
                    this.$refs.canvas.getContext('2d').scale(ratio, ratio);
                    this.signaturePad.clear();
                }
            }" @resize.window="resizeCanvas">
                <canvas x-ref="canvas" class="w-full h-32 border border-gray-300 rounded-md bg-gray-50"></canvas>
                <div class="flex justify-between mt-4">
                    <button @click="clear" class="px-4 py-2 bg-gray-200 rounded-md hover:bg-gray-300">Clear</button>
                    <button @click="save" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Save Initials</button>
                </div>
            </div>
            
            <form id="initialsForm" action="{{ route('contracts.dro.store-csr-initial', $contract->id) }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="initials" id="initialsInput">
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.2.0/dist/signature_pad.umd.min.js"></script>
@endsection