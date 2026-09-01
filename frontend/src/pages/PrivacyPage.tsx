function PrivacyPage() {
  return (
    <main className="flex-1 bg-stone-50 px-4 py-12 sm:px-6">
      <div className="mx-auto max-w-2xl rounded-lg border border-stone-200 bg-white px-8 py-10 shadow-sm sm:px-12">
        <h1 className="mb-8 text-2xl font-semibold tracking-tight text-stone-900">
          プライバシーポリシー
        </h1>
        <p className="mb-10 text-sm leading-relaxed text-stone-600">
          テクっと（以下「本サービス」といいます。）では、安心してサービスをご利用いただけるよう、利用者の情報を以下の方針に基づいて取り扱います。
        </p>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            1. 取得する情報
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            本サービスでは、サービスの提供に必要な範囲で、以下の情報を取得することがあります。
          </p>
          <ul className="list-disc space-y-1 pl-5 text-sm leading-relaxed text-stone-600">
            <li>名前</li>
            <li>メールアドレス</li>
            <li>RSSフィードの購読情報</li>
            <li>記事の既読、お気に入り、あとで読む等の利用情報</li>
            <li>利用者が登録したRSSフィードのURL</li>
            <li>Cookieやセッションなど、ログインやサービス利用に必要な情報</li>
            <li>お問い合わせの際に利用者から提供された情報</li>
          </ul>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            2. 情報の利用目的
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            取得した情報は、主に以下の目的で利用します。
          </p>
          <ul className="list-disc space-y-1 pl-5 text-sm leading-relaxed text-stone-600">
            <li>ユーザー登録、ログインなどのアカウント管理のため</li>
            <li>RSSフィードや記事を表示するため</li>
            <li>RSSフィードの購読状態を管理するため</li>
            <li>記事の既読、お気に入り、あとで読む等の状態を保存するため</li>
            <li>本サービスの改善や不具合対応のため</li>
            <li>お問い合わせへの対応のため</li>
            <li>不正利用の防止や安全性の確保のため</li>
          </ul>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            3. 外部サービスの利用
          </h2>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            本サービスでは、一部の機能を提供するために外部サービスを利用することがあります。
          </p>
          <h3 className="mb-2 text-sm font-semibold text-stone-700">
            DeepL API
          </h3>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            記事タイトル等の翻訳機能を提供するため、DeepL APIを利用します。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            外部サービスに送信された情報は、それぞれのサービス提供者のプライバシーポリシー等に基づいて取り扱われる場合があります。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            4. Cookie・セッションについて
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            本サービスでは、ログイン状態の維持、本人確認、セキュリティの確保などのために、Cookieやセッション情報を利用します。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            5. 第三者への提供について
          </h2>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            本サービスでは、法令に基づく場合などを除き、利用者本人の同意なく個人情報を第三者に提供することはありません。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            ただし、本サービスの提供に必要な範囲で、外部サービス提供者等に情報の取扱いを委託する場合があります。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            6. アカウント削除時の情報について
          </h2>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            利用者は、本サービスの設定画面から退会し、アカウントを削除できます。
          </p>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            アカウントを削除した場合、アカウント情報、RSSの購読情報、記事の既読・お気に入り・あとで読む等の利用者固有の情報は削除されます。
          </p>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            利用者が登録したRSSフィードについては、他の利用者も利用できる共有データとして残る場合があります。その場合でも、退会した利用者との紐付けは削除されます。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            また、そのRSSフィードから取得された記事についても、共有データとして残る場合があります。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            7. プライバシーポリシーの変更
          </h2>
          <p className="mb-4 text-sm leading-relaxed text-stone-600">
            本サービスの内容変更や、利用する外部サービスの追加・変更、法令の改正などに応じて、本ポリシーを変更することがあります。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            重要な変更がある場合は、本サービス上でお知らせします。
          </p>
        </section>

        <section>
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            8. お問い合わせ
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            本ポリシーに関するお問い合わせは、本サービスのお問い合わせページに記載している連絡先までお願いいたします。
          </p>
        </section>
      </div>
    </main>
  );
}

export default PrivacyPage;
