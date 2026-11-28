<x-legal-page title="Privacy Policy" :sections="[
    'who' => 'Who we are',
    'collect' => 'What we collect',
    'why' => 'Why we use it',
    'share' => 'Who can see your data',
    'cookies' => 'Cookies and storage',
    'keep' => 'How long we keep it',
    'security' => 'How we protect it',
    'rights' => 'Your rights',
    'children' => 'Children',
    'changes' => 'Changes to this policy',
    'contact' => 'Contact and complaints',
]">
    <p>
        This policy explains what personal data EventEase collects, why we use it, who can see it, and what rights you have under the
        Data Privacy Act of 2012 (Republic Act No. 10173) of the Philippines. By creating an account, you confirm that you have read it.
    </p>

    <section>
        <h2 id="who">1. Who we are</h2>
        <p>
            EventEase is a school project of {{ config('legal.school') }}. {{ config('legal.contact_name') }} manages the system
            and is responsible for your account data. When you register for an event, the organizer of that event also decides how your
            registration answers are used (see section 4).
        </p>
    </section>

    <section>
        <h2 id="collect">2. What we collect</h2>
        <ul>
            <li><strong>Account:</strong> your name, email, account type (participant or organizer), and your password, which is stored in a hashed form that nobody can read. We also keep when you accepted our Terms.</li>
            <li><strong>Registrations:</strong> the event, the category you chose (for example 21K), your answers to the organizer's registration form, your ticket code, the time you registered, and whether and when you were checked in.</li>
            <li><strong>Organizers:</strong> event details, posters, gallery photos, and the check-in records of their events.</li>
            <li><strong>Admin review:</strong> decisions and reasons for organizer accounts and events.</li>
            <li><strong>Technical data:</strong> a login session, a security token, and server logs (time, address, and errors) that help us fix problems.</li>
        </ul>
        <p>
            The registration form is written by the organizer, who may ask for details such as a phone number, a shirt size, or an emergency contact.
            Share only what you are comfortable with. Organizers should not ask for sensitive information, such as health details or government ID numbers,
            unless the event really needs it and the participant clearly agrees.
        </p>
    </section>

    <section>
        <h2 id="why">3. Why we use it</h2>
        <ul>
            <li>to create and protect your account;</li>
            <li>to let you register for events and receive a ticket;</li>
            <li>to let organizers manage their events, participants, and check-in, and see reports;</li>
            <li>to let admins review organizers and events, and prevent fake accounts and ticket scalping;</li>
            <li>to fix errors and improve the system.</li>
        </ul>
        <p>We use your data because you agreed to it and because it is needed to give you the service. We do not use it for advertising, and we do not sell it.</p>
    </section>

    <section>
        <h2 id="share">4. Who can see your data</h2>
        <ul>
            <li><strong>The organizer of an event you register for:</strong> your name, email, answers, ticket code, and check-in status. They can also download this as a CSV file for that event.</li>
            <li><strong>Admins:</strong> accounts, events, and registrations, so they can run the site.</li>
            <li><strong>The public:</strong> only what organizers publish about their events (title, date, place, poster, and gallery). Your registration is not shown to the public or to other participants.</li>
            <li><strong>Other services:</strong> an organizer may link an outside form, such as Google Forms. That service receives what you type there and has its own privacy policy.</li>
            <li><strong>Hosting:</strong> the company or computer that hosts EventEase stores the data for us.</li>
            <li><strong>The law:</strong> we may share data if a law or a court requires it.</li>
        </ul>
    </section>

    <section>
        <h2 id="cookies">5. Cookies and storage</h2>
        <p>
            EventEase uses only the cookies it needs to work: one to keep you logged in and one for security. On the check-in page, your browser also
            remembers your sound on or off choice. We do not use advertising or tracking cookies.
        </p>
    </section>

    <section>
        <h2 id="keep">6. How long we keep it</h2>
        <p>
            We keep your data while your account exists. When you delete your account on your Profile page, or an admin deletes it, your account and
            your registrations are deleted. Deleting an organizer account also deletes the events and registrations linked to it. Backups and server
            logs may keep copies for a short time.
        </p>
    </section>

    <section>
        <h2 id="security">7. How we protect it</h2>
        <p>
            Passwords are stored hashed. Organizer and admin pages need a login and the right account type, and an admin reviews every organizer
            account and event. No system is completely safe. If a serious breach affects your data, we will inform you and the National Privacy Commission
            as the law requires.
        </p>
    </section>

    <section>
        <h2 id="rights">8. Your rights</h2>
        <p>Under the Data Privacy Act you have the right to:</p>
        <ul>
            <li>be informed about how your data is used (this policy);</li>
            <li>access the data we hold about you;</li>
            <li>object to the use of your data;</li>
            <li>correct wrong data;</li>
            <li>ask us to erase, block, or stop using your data;</li>
            <li>receive a copy of your data in a usable form (data portability);</li>
            <li>claim damages if your data was misused;</li>
            <li>file a complaint.</li>
        </ul>
        <p>
            You can change your name and email, or delete your account, on your Profile page. For anything else, write to
            <a href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a>.
        </p>
    </section>

    <section>
        <h2 id="children">9. Children</h2>
        <p>
            EventEase is for people who are 18 or older. If you are younger, use it only with the permission and supervision of a parent or guardian.
            Parents and guardians can write to us to have a child's data deleted.
        </p>
    </section>

    <section>
        <h2 id="changes">10. Changes to this policy</h2>
        <p>We may update this policy. The date at the top shows the last change.</p>
    </section>

    <section>
        <h2 id="contact">11. Contact and complaints</h2>
        <p>
            Contact {{ config('legal.contact_name') }} at
            <a href="mailto:{{ config('legal.contact_email') }}">{{ config('legal.contact_email') }}</a>.
            If you think your data was handled wrongly, you can also complain to the National Privacy Commission of the Philippines
            at <a href="https://privacy.gov.ph" target="_blank" rel="noopener">privacy.gov.ph</a>.
        </p>
    </section>
</x-legal-page>