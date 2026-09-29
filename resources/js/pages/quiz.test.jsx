import { fireEvent, render, screen, waitFor } from '@testing-library/react';
import React from 'react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import Quiz from './quiz';
import { apiRequest } from '../lib/api';

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href, ...props }) => <a href={href} {...props}>{children}</a>,
    usePage: () => ({ props: { auth: { user: { username: 'TestDriver' } }, flash: {} } }),
}));

vi.mock('../lib/api', () => ({ apiRequest: vi.fn() }));

const questions = Array.from({ length: 2 }, (_, index) => ({
    id: index + 1,
    prompt: `Question ${index + 1}?`,
    options: [
        { key: 'a', label: 'Correct' },
        { key: 'b', label: 'Wrong' },
        { key: 'c', label: 'Third' },
        { key: 'd', label: 'Fourth' },
    ],
}));

describe('Quiz race flow', () => {
    beforeEach(() => {
        apiRequest.mockReset();
    });

    it('starts a race, locks feedback, advances, and shows classification', async () => {
        apiRequest
            .mockResolvedValueOnce({ attempt: { id: 7, difficulty: 'medium', total: 2 }, questions })
            .mockResolvedValueOnce({ question_id: 1, correct: true, correct_option: 'a', answered: 1, total: 2 })
            .mockResolvedValueOnce({ question_id: 2, correct: false, correct_option: 'a', answered: 2, total: 2 })
            .mockResolvedValueOnce({ correct_answers: 1, total_questions: 2, accuracy: 50, points_awarded: 20, total_points: 20 });

        render(<Quiz />);
        fireEvent.click(screen.getByRole('button', { name: /lights out/i }));
        expect(await screen.findByText('Question 1?')).toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: /correct/i }));
        expect(await screen.findByText(/sector clear/i)).toBeInTheDocument();
        fireEvent.click(screen.getByRole('button', { name: /next lap/i }));
        expect(await screen.findByText('Question 2?')).toBeInTheDocument();

        fireEvent.click(screen.getByRole('button', { name: /wrong/i }));
        expect(await screen.findByText(/position lost/i)).toBeInTheDocument();
        fireEvent.click(screen.getByRole('button', { name: /see classification/i }));

        await waitFor(() => expect(screen.getByText('Classification')).toBeInTheDocument());
        expect(screen.getByText('+20')).toBeInTheDocument();
        expect(apiRequest).toHaveBeenCalledTimes(4);
    });
});
