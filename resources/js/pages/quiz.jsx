import { Head, Link } from '@inertiajs/react';
import { ArrowRight, Check, ChevronLeft, Flag, LoaderCircle, RotateCcw, X } from 'lucide-react';
import { useState } from 'react';
import AppLayout from '../layouts/AppLayout';
import { apiRequest } from '../lib/api';

const modes = [
    { value: 'easy', title: 'Safety car', points: 10, note: 'Build confidence and warm the tyres.' },
    { value: 'medium', title: 'Race pace', points: 20, note: 'A balanced test across Formula racing history.' },
    { value: 'hard', title: 'Qualifying', points: 30, note: 'Technical details. No margin for error.' },
];

export default function Quiz() {
    const [phase, setPhase] = useState('setup');
    const [difficulty, setDifficulty] = useState('medium');
    const [attempt, setAttempt] = useState(null);
    const [questions, setQuestions] = useState([]);
    const [index, setIndex] = useState(0);
    const [feedback, setFeedback] = useState(null);
    const [result, setResult] = useState(null);
    const [busy, setBusy] = useState(false);
    const [error, setError] = useState('');

    const question = questions[index];

    async function startRace() {
        setBusy(true);
        setError('');
        try {
            const data = await apiRequest('/quiz/attempts', { method: 'POST', body: JSON.stringify({ difficulty }) });
            setAttempt(data.attempt);
            setQuestions(data.questions);
            setIndex(0);
            setFeedback(null);
            setPhase('racing');
        } catch (requestError) {
            setError(requestError.message);
        } finally {
            setBusy(false);
        }
    }

    async function answer(option) {
        if (feedback || busy) return;
        setBusy(true);
        setError('');
        try {
            const data = await apiRequest(`/quiz/attempts/${attempt.id}/answers`, {
                method: 'POST',
                body: JSON.stringify({ question_id: question.id, option }),
            });
            setFeedback({ selected: option, ...data });
        } catch (requestError) {
            setError(requestError.message);
        } finally {
            setBusy(false);
        }
    }

    async function advance() {
        if (index < questions.length - 1) {
            setIndex((value) => value + 1);
            setFeedback(null);
            setError('');
            return;
        }

        setBusy(true);
        try {
            const data = await apiRequest(`/quiz/attempts/${attempt.id}/complete`, { method: 'POST', body: '{}' });
            setResult(data);
            setPhase('complete');
        } catch (requestError) {
            setError(requestError.message);
        } finally {
            setBusy(false);
        }
    }

    function reset() {
        setPhase('setup');
        setAttempt(null);
        setQuestions([]);
        setFeedback(null);
        setResult(null);
        setError('');
    }

    return (
        <AppLayout title="Race control">
            <Head title="Race control" />
            {error && <div role="alert" className="mb-6 border-l-4 border-race-red bg-race-red/10 px-5 py-4 text-sm text-red-200">{error}</div>}

            {phase === 'setup' && (
                <section className="mx-auto max-w-5xl">
                    <div className="panel p-6 sm:p-10">
                        <p className="font-mono text-xs uppercase tracking-[0.3em] text-cyan">Select race programme</p>
                        <h2 className="mt-3 font-display text-4xl font-black uppercase italic tracking-tight">Choose your pace</h2>
                        <div className="mt-8 grid gap-4 md:grid-cols-3" role="radiogroup" aria-label="Quiz difficulty">
                            {modes.map((mode) => (
                                <button key={mode.value} role="radio" aria-checked={difficulty === mode.value} onClick={() => setDifficulty(mode.value)} className={`mode-card ${difficulty === mode.value ? 'mode-card-active' : ''}`}>
                                    <span className="font-mono text-xs uppercase tracking-[0.25em] text-slate-500">{mode.points} pts / correct</span>
                                    <span className="mt-8 block font-display text-2xl font-black uppercase italic">{mode.title}</span>
                                    <span className="mt-3 block text-sm leading-6 text-slate-400">{mode.note}</span>
                                </button>
                            ))}
                        </div>
                        <div className="mt-8 flex flex-wrap items-center justify-between gap-4 border-t border-white/10 pt-7">
                            <span className="text-sm text-slate-500">10 questions · answers lock immediately · points award on completion</span>
                            <button onClick={startRace} className="button-primary button-large" disabled={busy}>{busy ? <LoaderCircle className="animate-spin" size={18} /> : <Flag size={18} />} Lights out</button>
                        </div>
                    </div>
                </section>
            )}

            {phase === 'racing' && question && (
                <section className="mx-auto max-w-4xl">
                    <div className="mb-5 flex items-center justify-between font-mono text-xs uppercase tracking-[0.22em] text-slate-500"><span>{attempt.difficulty} programme</span><span>Lap {index + 1} / {questions.length}</span></div>
                    <div className="h-1 overflow-hidden bg-white/10"><div className="h-full bg-race-red transition-[width] duration-500" style={{ width: `${((index + 1) / questions.length) * 100}%` }} /></div>
                    <article className="panel mt-5 p-6 sm:p-10">
                        <p className="font-mono text-xs uppercase tracking-[0.26em] text-cyan">Question {String(index + 1).padStart(2, '0')}</p>
                        <h2 className="mt-5 text-2xl font-bold leading-snug sm:text-4xl">{question.prompt}</h2>
                        <div className="mt-9 grid gap-3 sm:grid-cols-2">
                            {question.options.map((option) => {
                                const isCorrect = feedback?.correct_option === option.key;
                                const isSelected = feedback?.selected === option.key;
                                const state = feedback ? (isCorrect ? 'answer-correct' : isSelected ? 'answer-wrong' : 'answer-muted') : '';
                                return (
                                    <button key={option.key} onClick={() => answer(option.key)} disabled={Boolean(feedback) || busy} className={`answer-card ${state}`}>
                                        <span className="answer-key">{option.key}</span><span>{option.label}</span>
                                        {isCorrect && <Check className="ml-auto" size={20} />}{isSelected && !isCorrect && <X className="ml-auto" size={20} />}
                                    </button>
                                );
                            })}
                        </div>
                        {feedback && (
                            <div className="mt-7 flex flex-col justify-between gap-4 border-t border-white/10 pt-6 sm:flex-row sm:items-center">
                                <p className={feedback.correct ? 'text-cyan' : 'text-red-300'}>{feedback.correct ? 'Sector clear. Correct answer.' : 'Position lost. Correct answer highlighted.'}</p>
                                <button onClick={advance} className="button-primary" disabled={busy}>{index === questions.length - 1 ? 'See classification' : 'Next lap'} <ArrowRight size={17} /></button>
                            </div>
                        )}
                    </article>
                </section>
            )}

            {phase === 'complete' && result && (
                <section className="mx-auto max-w-3xl text-center">
                    <div className="panel overflow-hidden p-7 sm:p-12">
                        <Flag className="mx-auto text-race-red" size={42} />
                        <p className="mt-6 font-mono text-xs uppercase tracking-[0.32em] text-cyan">Chequered flag</p>
                        <h2 className="mt-4 font-display text-5xl font-black uppercase italic tracking-[-0.05em] sm:text-7xl">Classification</h2>
                        <div className="mx-auto mt-9 grid max-w-xl grid-cols-3 gap-px bg-white/10">
                            {[['Correct', `${result.correct_answers}/${result.total_questions}`], ['Accuracy', `${result.accuracy}%`], ['Points', `+${result.points_awarded}`]].map(([label, value]) => <div key={label} className="bg-ink px-3 py-6"><span className="metric-label">{label}</span><strong className="mt-3 block font-display text-2xl font-black italic text-white sm:text-3xl">{value}</strong></div>)}
                        </div>
                        <p className="mt-7 text-slate-400">Career total: <strong className="text-white">{result.total_points} points</strong></p>
                        <div className="mt-9 flex flex-wrap justify-center gap-3"><button onClick={reset} className="button-primary"><RotateCcw size={17} /> Race again</button><Link href="/dashboard" className="button-ghost"><ChevronLeft size={17} /> Return to pit wall</Link></div>
                    </div>
                </section>
            )}
        </AppLayout>
    );
}
