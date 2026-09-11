import { Link } from "react-router-dom";

function TermsPage() {
  return (
    <main className="flex-1 bg-stone-50 px-4 py-12 sm:px-6">
      <div className="mx-auto max-w-2xl rounded-lg border border-stone-200 bg-white px-5 py-10 shadow-sm sm:px-12">
        <h1 className="mb-4 text-2xl font-semibold tracking-tight text-stone-900">
          利用規約
        </h1>
        <p className="mb-10 text-sm leading-relaxed text-stone-600">
          制定日：<time dateTime="2026-09-11">2026年9月11日</time>
        </p>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            1. 適用・サービス内容
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            この利用規約は、テクっと（以下「本サービス」といいます。）の利用条件を定めます。内容をご確認のうえ、本規約に同意してご利用ください。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            本サービスは、外部サイトのRSS / Atomから技術記事を取得し、記事の一覧・詳細表示、検索、既読・お気に入り・あとで読むなどの記事管理、機械翻訳などを提供します。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            2. アカウント
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            利用者は、登録情報を正確に保ち、メールアドレスやパスワードなどのアカウント情報を適切に管理してください。第三者へのアカウントの貸与・譲渡や、他者のアカウントの使用はしないでください。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            3. カスタムRSS
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            カスタムRSSの購読は、1アカウントにつき最大3件です。共通ソースはこの上限に含みません。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            4. 禁止事項
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            本サービスの利用にあたり、以下の行為を禁止します。
          </p>
          <ul className="list-disc space-y-1 pl-5 text-sm leading-relaxed text-stone-600">
            <li>不正アクセス、またはこれを試みる行為</li>
            <li>過度な負荷をかけるなど、サービス運営を妨害する行為</li>
            <li>他者へのなりすまし</li>
            <li>他者の著作権、プライバシーその他の権利を侵害する行為</li>
            <li>法令・公序良俗に反する行為</li>
            <li>その他、上記に準じ、サービスの安全な運営のために運営者が合理的な理由に基づき不適切と判断する行為</li>
          </ul>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            5. 外部記事・RSSと権利
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            本サービスは、外部サイトが配信するRSS / Atomを利用しています。外部記事の内容・正確性・安全性を保証するものではありません。
          </p>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            記事本文、タイトル、概要、画像などの著作権その他の権利は、各配信元または正当な権利者に帰属します。本サービスへの掲載によって、これらの権利が利用者に移ることはありません。利用の際は、法令と権利者が定める条件を守ってください。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            リンク先などの外部サイトを利用する際には、各サイトの利用規約等が適用される場合があります。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            6. 機械翻訳
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            英語記事のタイトル・概要には、DeepL等による機械翻訳を利用する場合があります。翻訳には誤りや意図と異なる表現が含まれることがあり、正確性は保証しません。正確な内容については元記事をご確認ください。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            7. サービスの変更・停止
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            保守、システムや通信の障害、外部サービスの仕様変更などにより、予告なく本サービスの一部または全部を変更・停止する場合があります。利用者への影響が大きい変更や停止については、可能な限り本サービス上でお知らせします。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            8. アカウント削除
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            利用者は、設定画面からアカウントを削除できます。削除後のアカウント情報、購読情報、記事の管理状態などのユーザーデータは復元できない場合があります。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            登録したRSSフィードや取得済みの記事は、利用者との紐付けを削除したうえで共有データとして残る場合があります。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            9. 個人情報の取扱い
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            利用者の個人情報は、
            <Link
              to="/privacy"
              className="text-emerald-700 underline underline-offset-2 hover:text-emerald-800 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
            >
              プライバシーポリシー
            </Link>
            に従って取り扱います。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            10. 免責
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            運営者は、本サービスの利用または利用不能によって生じた損害について、法令上認められる範囲で責任を負いません。ただし、運営者の故意・重大な過失による損害や、消費者契約法その他の法令により免責が認められない場合には、この免責は適用されません。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            11. 規約の変更
          </h2>
          <p className="mb-3 text-sm leading-relaxed text-stone-600">
            サービス内容や法令の変更などに応じて、必要な範囲で本規約を変更する場合があります。変更は、利用者一般の利益にかなう場合、または利用目的に反せず、必要性や内容などに照らして合理的な場合に、法令に従って行います。
          </p>
          <p className="text-sm leading-relaxed text-stone-600">
            変更する場合は、変更内容と効力発生日を、事前に本サービス上でお知らせします。法令上、利用者の同意が必要な変更については、同意を得たうえで適用します。
          </p>
        </section>

        <section className="mb-8">
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            12. 準拠法
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            本規約は日本法に準拠して解釈されます。
          </p>
        </section>

        <section>
          <h2 className="mb-3 text-base font-semibold text-stone-800">
            13. お問い合わせ
          </h2>
          <p className="text-sm leading-relaxed text-stone-600">
            本規約に関するご質問は、
            <Link
              to="/contact"
              className="text-emerald-700 underline underline-offset-2 hover:text-emerald-800 focus-visible:rounded focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-700/40"
            >
              お問い合わせページ
            </Link>
            に記載の連絡先までお願いいたします。
          </p>
        </section>
      </div>
    </main>
  );
}

export default TermsPage;
