<div>
    <div class="mb-4 flex justify-end">
        <button type="button" onclick="window.print()"
                class="inline-flex items-center rounded-lg bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 print:hidden">
            พิมพ์ / บันทึกเป็น PDF
        </button>
    </div>
    <article class="manual-content rounded-2xl bg-white p-6 sm:p-10 shadow-sm ring-1 ring-gray-900/5 text-gray-800 leading-relaxed">
        {!! $html !!}
    </article>

    <style>
        .manual-content h1 { font-size: 1.75rem; font-weight: 700; margin: 0 0 1rem; }
        .manual-content h2 { font-size: 1.35rem; font-weight: 700; margin: 2rem 0 .75rem; color: #be185d; }
        .manual-content h3 { font-size: 1.1rem; font-weight: 600; margin: 1.5rem 0 .5rem; }
        .manual-content p, .manual-content ul, .manual-content ol, .manual-content table, .manual-content pre { margin: 0 0 1rem; }
        .manual-content ul { list-style: disc; padding-left: 1.5rem; }
        .manual-content ol { list-style: decimal; padding-left: 1.5rem; }
        .manual-content table { width: 100%; border-collapse: collapse; font-size: .875rem; display: block; overflow-x: auto; }
        .manual-content th, .manual-content td { border: 1px solid #e5e7eb; padding: .4rem .6rem; text-align: left; }
        .manual-content th { background: #f9fafb; }
        .manual-content code { background: #f3f4f6; padding: .1rem .3rem; border-radius: .25rem; font-size: .85em; }
        .manual-content pre { background: #f3f4f6; padding: .75rem; border-radius: .5rem; overflow-x: auto; }
        .manual-content hr { margin: 1.5rem 0; border-color: #e5e7eb; }
        .manual-content a { color: #db2777; text-decoration: underline; }
    </style>
</div>
