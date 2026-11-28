<x-legal-page title="Terms and Conditions" :sections="[
    'about' => 'About EventEase',
    'accounts' => 'Your account',
    'organizers' => 'Organizer accounts',
    'events' => 'Events and registration',
    'tickets' => 'Tickets and check-in',
    'use' => 'Acceptable use',
    'content' => 'Content you upload',
    'admins' => 'What admins can do',
    'payments' => 'No payments',
    'liability' => 'Disclaimers and liability',
    'changes' => 'Changes and ending',
    'law' => 'Governing law',
]">
    <p>
        Welcome to EventEase. By creating an account or using the site, you agree to these Terms and Conditions
        and to our <a href="{{ route('privacy') }}">Privacy Policy</a>. If you do not agree, please do not use EventEase.
    </p>

    <section>
        <h2 id="about">1. About EventEase</h2>
        <p>
            EventEase is an online system for event registration and management, for events such as concerts, fun runs,
            and festivals. It is a school project made by students of {{ config('legal.school') }}.
        </p>
        <p>
            Organizers publish events. Participants register for them and receive a digital ticket with a QR code.
            EventEase is not the organizer of the events on the site, and it does not sell tickets.
        </p>
    </section>

    <section>
        <h2 id="accounts">2. Your account</h2>
        <ul>
            <li>You must be 18 or older. If you are younger, use EventEase only with the permission and supervision of a parent or guardian.</li>
            <li>Give true and current information when you register.</li>
            <li>One person, one account. Do not make several accounts.</li>
            <li>Keep your password secret. You are responsible for what happens in your account. Tell us quickly if someone else uses it.</li>
            <li>We may suspend or delete an account that breaks these Terms.</li>
        </ul>
    </section>

    <section>
        <h2 id="organizers">3. Organizer accounts</h2>
        <p>
            A new organizer account must be approved by an admin before it can create events. An admin may approve or
            decline the request, and may change the decision later. Approval does not mean that EventEase endorses the organizer.
        </p>
        <p>Organizers promise that they will:</p>
        <ul>
            <li>give correct information about the event (date, time, place, slots, and rules);</li>
            <li>run only lawful events;</li>
            <li>ask participants only for the information they need for the event;</li>
            <li>use participants' information only for that event, keep it safe, and follow the Data Privacy Act of 2012 (Republic Act No. 10173).</li>
        </ul>
    </section>

    <section>
        <h2 id="events">4. Events and registration</h2>
        <ul>
            <li>Every new event is reviewed by an admin before it is public. An admin may decline an event, or take it down later, and gives a reason.</li>
            <li>You can register while the registration is open (until the deadline, or until the event starts if there is no deadline) and while slots are left.</li>
            <li>Registering does not guarantee entry if you break the organizer's rules.</li>
            <li>The organizer may change or cancel an event. EventEase is not responsible for these changes. Please contact the organizer.</li>
        </ul>
    </section>

    <section>
        <h2 id="tickets">5. Tickets and check-in</h2>
        <ul>
            <li>One ticket per person for each event.</li>
            <li>A ticket is personal. It cannot be transferred, sold, or resold.</li>
            <li>Do not post your ticket or QR code in public. Anyone who has it may be able to use it.</li>
            <li>At the event, the organizer may check your ticket and a valid ID.</li>
            <li>A ticket may be refused or cancelled if it was obtained by breaking these Terms, for example with several accounts.</li>
        </ul>
    </section>

    <section>
        <h2 id="use">6. Acceptable use</h2>
        <p>Do not use EventEase to:</p>
        <ul>
            <li>break the law or the rights of other people;</li>
            <li>give false information, or use someone else's identity;</li>
            <li>get more than one ticket for an event, or buy and sell tickets for profit (scalping);</li>
            <li>try to enter accounts, admin pages, or data that are not yours;</li>
            <li>upload viruses, or overload or copy the site automatically;</li>
            <li>harass others, or post content that is illegal, hateful, or sexual.</li>
        </ul>
    </section>

    <section>
        <h2 id="content">7. Content you upload</h2>
        <p>
            Organizers keep ownership of the posters, photos, gallery images, and text they upload. By uploading, you give
            EventEase permission to store and show this content on the site for your event. You promise that you have the
            right to use it, including the permission of people who are clearly shown in photos. We may remove content that
            breaks these Terms.
        </p>
    </section>

    <section>
        <h2 id="admins">8. What admins can do</h2>
        <p>
            Admins may review, approve, decline, hide, or delete events and accounts to keep EventEase safe and correct.
            Deleting an account also deletes the registrations and events linked to it. Deleted data cannot be restored.
        </p>
    </section>

    <section>
        <h2 id="payments">9. No payments</h2>
        <p>
            EventEase does not take payments, and tickets have no money value. If an organizer asks for a fee, it is arranged
            between you and the organizer, outside EventEase. EventEase is not part of that agreement.
        </p>
    </section>

    <section>
        <h2 id="liability">10. Disclaimers and liability</h2>
        <p>
            EventEase is a school project. It is provided "as is", and we do not promise that it will always work or have no errors.
            We are not responsible for the events, the organizers, or what happens at an event. As far as the law allows, the
            EventEase team is not liable for loss or damage from using the site. Nothing in these Terms removes rights that you have
            under Philippine law.
        </p>
    </section>

    <section>
        <h2 id="changes">11. Changes and ending</h2>
        <p>
            We may update these Terms. The date at the top shows the last change. If you keep using EventEase after a change, you accept
            the new Terms. You can stop using EventEase at any time, and you can delete your account on your Profile page.
            We may also end or change the service.
        </p>
    </section>

    <section>
        <h2 id="law">12. Governing law</h2>
        <p>These Terms follow the laws of the Republic of the Philippines.</p>
    </section>
</x-legal-page>