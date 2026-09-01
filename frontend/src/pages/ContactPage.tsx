function ContactPage() {
  return (
    <main className="flex flex-1 items-start justify-center bg-stone-50 px-4 py-12">
      <div className="w-full max-w-sm rounded-lg border border-stone-200 bg-white p-10 shadow-sm sm:max-w-md lg:max-w-lg">
        <h1 className="mb-6 text-xl font-semibold tracking-tight text-stone-900">
          お問い合わせ
        </h1>
        <p className="mb-4 text-sm leading-relaxed text-stone-600">
          ご不明な点やご要望がございましたら、以下のメールアドレスまでお気軽にご連絡ください。
        </p>
        <a
          href="mailto:nasumasa.contact@gmail.com"
          className="text-sm font-medium text-emerald-700 hover:text-emerald-800 hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40 focus-visible:rounded"
        >
          nasumasa.contact@gmail.com
        </a>
        <p className="mt-6 text-sm leading-relaxed text-stone-500">
          お問い合わせ内容によっては、返信までお時間をいただく場合があります。
        </p>
      </div>
    </main>
  );
}

export default ContactPage;
